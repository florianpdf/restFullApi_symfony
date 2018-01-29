<?php

namespace AppBundle\Controller;

use AppBundle\Entity\User;
use AppBundle\Form\UserType;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\View\View;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class FOSRestApiController
 *
 * @package AppBundle\Controller
 *
 * @Rest\Route("fosapi")
 */
class FOSRestApiController extends Controller{

    /**
     * Get all users
     *
     * @Rest\Get("/users")
     */
    public function getAction()
    {
        $em = $this->getDoctrine()->getManager();
        $users = $em->getRepository(User::class)->findAll();

        if ($users === null) {
            return new View("there are no users exist", Response::HTTP_NOT_FOUND);
        }
        return $users;
    }

    /**
     * Get one user
     *
     * @Rest\Get("/users/{id}")
     */
    public function idAction($id)
    {
        $user = $this->getDoctrine()->getRepository(User::class)->findOneById($id);
        if ($user === null) {
            return new View("user not found", Response::HTTP_NOT_FOUND);
        }
        return $user;
    }

    /**
     * Create new user
     *
     * @Rest\View(statusCode=Response::HTTP_CREATED)
     * @Rest\Post("/users")
     */
    public function postUsersAction(Request $request)
    {
        $user = new User();
        $form = $this->createForm(UserType::class, $user);

        $form->submit($request->request->all());

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($user);
            $em->flush();
            return $user;
        } else {
            return $form;
        }
    }

    /**
     * Delete one user
     *
     * @Rest\View(statusCode=Response::HTTP_NO_CONTENT)
     * @Rest\Delete("/users/{id}")
     */
    public function removeUserAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $user = $em->getRepository('AppBundle:User')->findOneById($request->get('id'));

        if ($user) {
            $em->remove($user);
            $em->flush();
            return new JsonResponse(
                array(
                    'message' => 'User delete'
                ),
                Response::HTTP_ACCEPTED);
        } else{
            return new JsonResponse(
                array(
                    'id' => $request->get('id'),
                    'message' => 'User not found'
                ),
                Response::HTTP_NOT_FOUND);
        }
    }

    /**
     * @Rest\View()
     * @Rest\Put("/users/{id}")
     */
    public function patchPlaceAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $user = $em->getRepository('AppBundle:User')->findOneById($request->get('id'));

        if (empty($user)) {
            return new JsonResponse(['message' => 'User not found'], Response::HTTP_NOT_FOUND);
        }

        $form = $this->createForm(UserType::class, $user);

        $form->submit($request->request->all(), false);

        if ($form->isValid()) {
            $em->merge($user);
            $em->flush();
            return $user;
        } else {
            return $form;
        }
    }
}