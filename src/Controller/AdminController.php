<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\TradingPair;
use App\Entity\AuditLog;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;

#[Route('/admin')]
#[IsGranted('ROLE_ADMIN')]
class AdminController extends AbstractController
{
    private EntityManagerInterface $entityManager;
    private CsrfTokenManagerInterface $csrfTokenManager;

    public function __construct(EntityManagerInterface $entityManager, CsrfTokenManagerInterface $csrfTokenManager)
    {
        $this->entityManager = $entityManager;
        $this->csrfTokenManager = $csrfTokenManager;
    }

    #[Route('/dashboard', name: 'admin_dashboard', methods: ['GET'])]
    public function dashboard(): Response
    {
        try {
            $repoUser = $this->entityManager->getRepository(User::class);
            $repoPair = $this->entityManager->getRepository(TradingPair::class);

            $stats = [
                'total_users' => $repoUser->count([]),
                'active_users' => $repoUser->count(['status' => 'ACTIVE']),
                'total_pairs' => $repoPair->count([]),
                'active_pairs' => $repoPair->count(['isActive' => true]),
            ];

            return $this->render('admin/dashboard.html.twig', [
                'stats' => $stats,
                'title' => 'Admin Dashboard'
            ]);
        } catch (\Exception $e) {
            $this->addFlash('error', 'An error occurred while loading the dashboard.');
            return $this->redirectToRoute('app_home');
        }
    }

    #[Route('/users', name: 'admin_users', methods: ['GET'])]
    public function users(Request $request): Response
    {
        try {
            $page = max(1, $request->query->getInt('page', 1));
            $limit = 20;

            $users = $this->entityManager->getRepository(User::class)
                ->createQueryBuilder('u')
                ->orderBy('u.createdAt', 'DESC')
                ->setFirstResult(($page - 1) * $limit)
                ->setMaxResults($limit)
                ->getQuery()
                ->getResult();

            $totalUsers = $this->entityManager->getRepository(User::class)->count([]);

            return $this->render('admin/users.html.twig', [
                'users' => $users,
                'currentPage' => $page,
                'totalPages' => ceil($totalUsers / $limit),
                'title' => 'User Management'
            ]);
        } catch (\Exception $e) {
            $this->addFlash('error', 'An error occurred while loading users.');
            return $this->redirectToRoute('admin_dashboard');
        }
    }

    #[Route('/trading-pairs', name: 'admin_trading_pairs', methods: ['GET', 'POST'])]
    public function tradingPairs(Request $request): Response
    {
        try {
            if ($request->isMethod('POST')) {
                $pairId = $request->request->get('pair_id');
                $action = $request->request->get('action');
                $csrfToken = $request->request->get('_token');

                if (!$this->isCsrfTokenValid('trading_pair_action', $csrfToken)) {
                    throw new \RuntimeException('Invalid CSRF token.');
                }

                $pair = $this->entityManager->getRepository(TradingPair::class)->find($pairId);

                if (!$pair) {
                    throw new NotFoundHttpException('Trading pair not found');
                }

                if ($action === 'toggle') {
                    $pair->setIsActive(!$pair->getIsActive());
                    $this->entityManager->flush();
                    $this->addFlash('success', 'Trading pair status updated successfully.');
                }
            }

            $pairs = $this->entityManager->getRepository(TradingPair::class)
                ->findBy([], ['createdAt' => 'DESC']);

            return $this->render('admin/trading_pairs.html.twig', [
                'pairs' => $pairs,
                'title' => 'Trading Pairs Management'
            ]);
        } catch (NotFoundHttpException|\RuntimeException $e) {
            $this->addFlash('error', $e->getMessage());
            return $this->redirectToRoute('admin_trading_pairs');
        } catch (\Exception $e) {
            $this->addFlash('error', 'An error occurred while managing trading pairs.');
            return $this->redirectToRoute('admin_dashboard');
        }
    }

    #[Route('/audit-logs', name: 'admin_audit_logs', methods: ['GET'])]
    public function auditLogs(Request $request): Response
    {
        try {
            $page = max(1, $request->query->getInt('page', 1));
            $limit = 50;

            $logs = $this->entityManager->getRepository(AuditLog::class)
                ->createQueryBuilder('l')
                ->orderBy('l.createdAt', 'DESC')
                ->setFirstResult(($page - 1) * $limit)
                ->setMaxResults($limit)
                ->getQuery()
                ->getResult();

            $totalLogs = $this->entityManager->getRepository(AuditLog::class)->count([]);

            return $this->render('admin/audit_logs.html.twig', [
                'logs' => $logs,
                'currentPage' => $page,
                'totalPages' => ceil($totalLogs / $limit),
                'title' => 'Audit Logs'
            ]);
        } catch (\Exception $e) {
            $this->addFlash('error', 'An error occurred while loading audit logs.');
            return $this->redirectToRoute('admin_dashboard');
        }
    }

    #[Route('/user/{id}/toggle', name: 'admin_user_toggle', methods: ['POST'])]
    public function toggleUserStatus(Request $request, int $id): Response
    {
        try {
            $csrfToken = $request->request->get('_token');
            if (!$this->isCsrfTokenValid('toggle_user_' . $id, $csrfToken)) {
                throw new \RuntimeException('Invalid CSRF token.');
            }

            $user = $this->entityManager->getRepository(User::class)->find($id);

            if (!$user) {
                throw new NotFoundHttpException('User not found');
            }

            $newStatus = $user->getStatus() === 'ACTIVE' ? 'SUSPENDED' : 'ACTIVE';
            $user->setStatus($newStatus);

            $this->entityManager->flush();

            $this->addFlash('success', "User status updated to {$newStatus}");
            return $this->redirectToRoute('admin_users');
        } catch (NotFoundHttpException|\RuntimeException $e) {
            $this->addFlash('error', $e->getMessage());
            return $this->redirectToRoute('admin_users');
        } catch (\Exception $e) {
            $this->addFlash('error', 'An error occurred while updating user status.');
            return $this->redirectToRoute('admin_users');
        }
    }
}
