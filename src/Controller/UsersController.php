<?php

namespace App\Controller;

use App\Entity\Grade;
use App\Entity\Users;
use App\Form\RegisterType;
use App\Repository\GradeRepository;
use App\Repository\UsersRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class UsersController extends AbstractController
{
    /**
     * @Route("/login", name="login")
     * @param AuthenticationUtils $utils
     * @return Response
     */
    public function login(AuthenticationUtils $utils)
    {
        $error = $utils->getLastAuthenticationError();
        $username = $utils->getLastUsername();
        return $this->render('users/login.html.twig',[
            'hasError' => $error !== null,
            'username' => $username
        ]);
    }

    /**
     * @Route("/logout", name="logout")
     */
    public function logout()
    {

    }

    /**
     * @Route("/register", name="register")
     * @param Request $request
     * @param EntityManagerInterface $manager
     * @param UserPasswordEncoder $encoder
     * @return Response
     */
    public function register(Request $request, EntityManagerInterface $manager, UserPasswordEncoderInterface $encoder)
    {
        $user = new Users();
        $form = $this->createForm(RegisterType::class, $user);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $pwd = $encoder->encodePassword($user, $user->getPwd());
            $baseRole = $this->getDoctrine()->getManager()->getRepository(Grade::class)->findOneBy(['name'=>'ROLE_NoValid']);
            $user->setGrade($baseRole);
            $user->setPwd($pwd);
            $manager->persist($user);
            $manager->flush();
            $this->addFlash(
                'success',
                'Demande de compte effectuée'
            );
            return $this->redirectToRoute('homepage');
        }

        return $this->render('users/register.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/forgotPassword", name="forgot")
     * @return Response
     */
    public function forgotPwd()
    {
        return $this->render('users/forgot.html.twig');
    }


    /**
     * permet de lister les membres
     * @Route("/effectif", name="effectif")
     * @param UsersRepository $repository
     * @return Response
     */
    public function effectif(UsersRepository $usersRepo, GradeRepository $gradeRepo)
    {
        $membreList = $usersRepo->findLspdMember();
        $gradeList = $gradeRepo->findByRangSup(-1);
        $noValidUsers = $usersRepo->findNoValid();
        return $this->render('home/effectif.html.twig', [
            'membres' => $membreList,
            'grades' => $gradeList,
            'noValidUsers' => $noValidUsers
        ]);
    }

    /**
     * Permet de valider un utilisateur
     * @Route("/effectif/validator/{id}", name="userValidator")
     */
    public function ValidUser(Users $user, GradeRepository $repo, EntityManagerInterface $manager)
    {
        $gradeNoValid = $this->getDoctrine()->getManager()->getRepository(Grade::class)->findOneBy(['name' => 'ROLE_Valid']);
        $user->setGrade($gradeNoValid);
        $manager->persist($user);
        $manager->flush();
        return $this->redirectToRoute('effectif');
    }

    /**
     * Remove user
     * @Route("/effectif/del/{id}", name="userRemove")
     * @param Users $user
     * @param EntityManagerInterface $manager
     * @return RedirectResponse
     */
    public function delUser(Users $user, EntityManagerInterface $manager)
    {
        $manager->remove($user);
        $manager->flush();
        return $this->redirectToRoute('effectif');
    }


    /**
     * Permet de changer le grade
     * @Route("/effectif/changeGrade", name="switchGrade")
     * @param UsersRepository $repo
     * @param EntityManagerInterface $manager
     * @return RedirectResponse
     */
    public function switchGrade(UsersRepository $repo, EntityManagerInterface $manager)
    {
        $userId = htmlspecialchars(trim($_POST['id']));
        $gradeForm = htmlspecialchars(trim($_POST['grade']));
        $newGrade = $this->getDoctrine()->getManager()->getRepository(Grade::class)->findOneBy(['name' => $gradeForm]);
        $userMod = $repo->findOneBy(['id' => $userId]);
        $userMod->setGrade($newGrade);
        $manager->persist($userMod);
        $manager->flush();
        return $this->redirectToRoute('effectif');
    }

    /**
     * Permet d'ajouter un grade
     * @Route("/effectif/addGrade", name="addGrade")
     * @param EntityManagerInterface $manager
     * @return RedirectResponse
     */
    public function addGrade(EntityManagerInterface $manager)
    {
        $nameGrade = htmlspecialchars(trim($_POST['name']));
        $orderGrade = htmlspecialchars(trim($_POST['order']));
        $grade = new Grade();
        $grade
            ->setName('ROLE_'.$nameGrade)
            ->setRang($orderGrade)
            ->setSuperAdmin(0);
        $manager->persist($grade);
        $manager->flush();
        return $this->redirectToRoute('effectif');
    }

    /**
     * Permet de supprimer un grade
     * @Route("/effectif/delGrade/{id}", name="rmGrade")
     * @param Grade $grade
     * @param EntityManagerInterface $manager
     * @return RedirectResponse
     */
    public function removeGrade(Grade $grade, EntityManagerInterface $manager)
    {
        $manager->remove($grade);
        $manager->flush();
        return $this->redirectToRoute('effectif');
    }

    /**
     * Permet de réduire l'ordre d'un grade
     * @Route("/effectif/downGrade/{id}", name="downGrade")
     * @param Grade $grade
     * @param EntityManagerInterface $manager
     * @return RedirectResponse
     */
    public function downGrade(Grade $grade, EntityManagerInterface $manager)
    {
        $before = $grade->getRang();
        $new = $before-1;
        if ($new == -1) {
            $this->addFlash(
                'danger',
                'Impossible de dessendre ce grade'
            );
            return $this->redirectToRoute('effectif');
        }
        $grade->setRang($new);
        $manager->persist($grade);
        $manager->flush();
        return $this->redirectToRoute('effectif');
    }

    /**
     * Augmenter le rang d'un grade
     * @Route("/effectif/upGrade/{id}", name="upGrade")
     * @param Grade $grade
     * @param EntityManagerInterface $manager
     * @return RedirectResponse
     */
    public function upGrade(Grade $grade, EntityManagerInterface $manager)
    {
        $before = $grade->getRang();
        $new = $before+1;
        $grade->setRang($new);
        $manager->persist($grade);
        $manager->flush();
        return $this->redirectToRoute('effectif');
    }

    /**
     * Toggle admin
     * @Route("/effectif/switchAdmin/{id}", name="toggleAdmin")
     * @param Grade $grade
     * @param EntityManagerInterface $manager
     * @return RedirectResponse
     */
    public function switchAdmin(Grade $grade, EntityManagerInterface $manager)
    {
        $before = $grade->getSuperAdmin();
        if ($before){
            $before = false;
        }else{
            $before = true;
        }
        $grade->setSuperAdmin($before);
        $manager->persist($grade);
        $manager->flush();
        return $this->redirectToRoute('effectif');
    }
}
