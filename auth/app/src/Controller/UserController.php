<?php


namespace App\Controller;


use App\Entity\User;
use App\Form\AdminUserType;
use App\Repository\UserRepository;
use App\Service\PaginationService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class UserController
 *
 * @Route("/admin/user", name="admin_user_")
 * @package App\Controller\Admin
 */
class UserController extends AbstractController
{
    /**
     * @Route("/", name="index")
     */
    public function index(UserRepository $userRepository, PaginationService $paginator): Response
    {
        $usersQuery = $userRepository->getAllQuery();
        $users = $paginator->paginate($usersQuery, 15);
        return $this->render('admin/user/index.html.twig', [
            'users'=>$users
        ]);
    }

    /**
     * @Route("/{user}/edit", name="edit")
     */
    public function edit(int $user, Request  $request, UserRepository $userRepository)
    {
        $user = $userRepository->find($user);
        $form = $this->createForm(AdminUserType::class, $user, [
            'roles'=>$user->getRoles()
        ]);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()){
            $user = $form->getData();
            $em = $this->getDoctrine()->getManager();
            $em->persist($user);
            $em->flush();
            return $this->redirectToRoute("admin_user_index");
        }
        return $this->render("admin/user/edit.html.twig", [
            'user'=> $user,
            'form'=>$form->createView()
        ]);

    }
}