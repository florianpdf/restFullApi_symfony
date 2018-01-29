<?php

namespace AppBundle\Controller;

use AppBundle\Entity\User;
use AppBundle\Form\UserType;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\View\View;
use Nelmio\ApiDocBundle\Annotation\Model;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Swagger\Annotations as SWG;


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
     *
     * @SWG\Response(
     *     response=202,
     *     description="Return all user",
     *     @SWG\Schema(
     *         type="array",
     *         @Model(type=User::class)
     *     )
     * )
     *
     * @SWG\Tag(name="Show users")
     */
    public function getAllUsersAction()
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
     *
     * @SWG\Response(
     *     response=202,
     *     description="Returns one user",
     *     @SWG\Schema(
     *         type="array",
     *         @Model(type=User::class)
     *     )
     * )
     * @SWG\Parameter(
     *     name="id",
     *     in="query",
     *     type="number",
     *     description="The id of user you want to see"
     * )
     * @SWG\Tag(name="Show user")
     */
    public function getOneUserAction($id)
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
     *
     * @SWG\Response(
     *     response=202,
     *     description="Create user",
     *     @SWG\Schema(
     *         type="array",
     *         @Model(type=User::class)
     *     )
     * )
     *
     * @SWG\Parameter(
     *     name="name",
     *     in="formData",
     *     type="string",
     *     description="Name of user"
     * )
     * @SWG\Parameter(
     *     name="firstname",
     *     in="formData",
     *     type="string",
     *     description="Firstname of user"
     * )
     * @SWG\Parameter(
     *     name="email",
     *     in="formData",
     *     type="string",
     *     description="Email of user"
     * )
     * @SWG\Parameter(
     *     name="picture",
     *     in="formData",
     *     type="string",
     *     description="Picture of user"
     * )
     * @SWG\Parameter(
     *     name="but",
     *     in="formData",
     *     type="string",
     *     description="But of user"
     * )
     *
     * @SWG\Tag(name="Show user")
     */
    public function createUsersAction(Request $request)
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
     *
     * @SWG\Response(
     *     response=202,
     *     description="Delete one user",
     *     @SWG\Schema(
     *         type="array",
     *         @Model(type=User::class)
     *     )
     * )
     * @SWG\Parameter(
     *     name="id",
     *     in="query",
     *     type="number",
     *     description="The id of user you want to delete"
     * )
     * @SWG\Tag(name="Delete user")
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
    public function updateUserAction(Request $request)
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