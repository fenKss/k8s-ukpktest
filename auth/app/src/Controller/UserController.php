<?php

namespace App\Controller;

use App\Entity\Image;
use App\Entity\User;
use App\Entity\UserTest;
use App\ENum\EImageType;
use App\ENum\EUserTestStatus;
use App\Form\UserFormType;
use App\lib\FS\Exceptions\FileNotExistException;
use App\lib\FS\FS;
use App\Repository\OlympicRepository;
use Carbon\Carbon;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class UserController
 * @Route("/user", name="user_")
 *
 * @package App\Controller
 */
class UserController extends AbstractController
{

    /**
     * @Route("/", name="index")
     */
    public function index(OlympicRepository $OlympicRepository): Response
    {
        /**
         * @var ?User $user
         */
        $user = $this->getUser();
        if (!$user) {
            return $this->redirectToRoute('default');
        }
        $em = $this->getDoctrine()->getManager();
        foreach ($user->getUserTests() as $userTest) {
            $tour   = $userTest->getVariant()->getTest()->getTour();
            $now    = new Carbon();
            $status = $userTest->getStatus();
            if ($now > $tour->getStartedAt() && $now < $tour->getExpiredAt()
                && $status == EUserTestStatus::PAID_TYPE
            ) {
                if ($status != EUserTestStatus::STARTED_TYPE) {
                    $userTest->setStatus(EUserTestStatus::STARTED_TYPE);
                    $em->persist($userTest);
                }
            } elseif ($now >= $tour->getExpiredAt()) {
                if ($status != EUserTestStatus::FINISHED_TYPE) {
                    $userTest->setStatus(EUserTestStatus::FINISHED_TYPE);
                    $em->persist($userTest);
                }
            }
        }
        $em->flush();
        $olympics = $OlympicRepository->getByUser($user);

        return $this->render('user/index.html.twig', [
            'user'     => $user,
            'olympics' => $olympics
        ]);
    }

    /**
     * @Route("/{userTest}/results", name="results")
     */
    public function results(UserTest $userTest): Response
    {
        /**
         * @var User $user
         */
        $user = $this->getUser();

        if (!$user || $userTest->getUser()->getId() !== $user->getId() || $userTest->getStatus() != EUserTestStatus::FINISHED_TYPE) {
            return $this->redirectToRoute('user_index');
        }
        return $this->render('test/results.html.twig', [
            'userTest' => $userTest,
            'answers'  => json_decode($userTest->getResultJson(), true)['answers']
        ]);
    }

    /**
     * @Route("/edit", name="edit")
     * @throws FileNotExistException
     */
    public function edit(Request $request): Response
    {
        /**
         * @var ?User $user
         */
        $user = $this->getUser();
        if (!$user) {
            return $this->redirectToRoute('user_index');
        }

        $form = $this->createForm(UserFormType::class, $user, [
            'allow_extra_fields' => true,
            'attr'               => [
                'class' => 'form'
            ]
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user       = $form->getData();
            $em         = $this->getDoctrine()->getManager();
            $avatarFile = $form->get('avatarFile')->getData();
            //just 1 file in array
//            if ($avatarFile && isset($avatarFile[0])) {
//                /**
//                 * @var UploadedFile $avatarFile
//                 */
//                $avatarFile = $avatarFile[0];
//
//                $image = new Image();
//
//                $filename  = FS::generateRandomString(15);
//                $extension = explode(".", $avatarFile->getClientOriginalName());
//                $extension = end($extension);
//                $path      = (self::AVATAR_DIR . $filename . "." . $extension);
//
//                $fullPathDir = $this->projectPublicDir . self::AVATAR_DIR;
//                $fullPath    = $this->projectPublicDir . $path;
//
//                if (!FS::isDir($fullPathDir)) {
//                    FS::mkdir($fullPathDir);
//                }
//                $image->setSize($avatarFile->getFileInfo()->getSize());
//                $image->setFilename($filename);
//                $image->setPath($path);
//                $image->setFullPath($this->projectPublicDir . $path);
//                $image->setType(EImageType::AVATAR_TYPE);
//                $image->setExtension($extension);
//
//                if (move_uploaded_file($avatarFile->getRealPath(), $fullPath)) {
//                    $userAvatar = $user->getAvatar();
//                    if ($userAvatar) {
//                        $user->setAvatar(null);
//                        $em->persist($user);
//                        $em->remove($userAvatar);
//
//                        FS::rm($userAvatar->getFullPath());
//
//                    }
//
//                    $user->setAvatar($image);
//                    $em->persist($image);
//                } else {
//                    return $this->render('user/edit.html.twig', [
//                        'user'  => $user,
//                        'form'  => $form->createView(),
//                        'error' => 'не удалось загрузить аватар'
//                    ]);
//                }
//            }
            $em->persist($user);
            $em->flush();
            return $this->redirectToRoute("user_index");
        }
        return $this->render('user/edit.html.twig', [
            'user'  => $user,
            'form'  => $form->createView(),
            'error' => ''
        ]);
    }
}
