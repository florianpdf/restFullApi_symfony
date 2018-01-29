<?php

namespace AppBundle\Controller;

use AppBundle\Entity\User;
use AppBundle\Form\UserType;
use Nelmio\ApiDocBundle\Annotation\Model;
use Psr\Log\LoggerInterface;
use Swagger\Annotations as SWG;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;

/**
 * Class ApiController
 *
 * @package AppBundle\Controller
 *
 * @Route("api")
 */
class ApiController extends Controller
{

    /**
     * Serialize an object
     *
     * @param $object
     *
     * @return string
     */
    private function serialize($object){
        $encoders = array(new JsonEncoder());
        $normalizers = array(new ObjectNormalizer());

        $serializer = new Serializer($normalizers, $encoders);

        return $serializer->serialize($object, 'json');
    }

    /**
     * Get form error
     *
     * @param \Symfony\Component\Form\FormInterface $form
     *
     * @return array
     */
    private function getErrorsFromForm(FormInterface $form)
    {
        $errors = array();
        foreach ($form->getErrors() as $error) {
            $errors[] = $error->getMessage();
        }
        foreach ($form->all() as $childForm) {
            if ($childForm instanceof FormInterface) {
                if ($childErrors = $this->getErrorsFromForm($childForm)) {
                    $errors[$childForm->getName()] = $childErrors;
                }
            }
        }
        return $errors;
    }

    /**
     *
     * List Users.
     *
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     *
     * @Route("/users", name="users_get_all", methods={"GET"})
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
    public function getUsersAction(){
        $em = $this->getDoctrine()->getManager();
        $users = $em->getRepository(User::class)->findAll();

        return new JsonResponse($this->serialize($users), Response::HTTP_ACCEPTED, [], true);
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     *
     * @Route("/users/{id}", name="users_get_one", methods={"GET"})
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
    public function getOneUserAction(Request $request){
        $em = $this->getDoctrine()->getManager();
        $user = $em->getRepository(User::class)->findOneById($request->get('id'));

        if ($user){
            return new JsonResponse($this->serialize($user), Response::HTTP_ACCEPTED, [],  true);
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
     * @Route("/users", name="users_add")
     *
     * @Method("POST")
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
    public function addUserAction(Request $request, LoggerInterface $logger)
    {
        $user = new User();
        $form = $this->createForm(UserType::class, $user);
        $form->submit($request->request->all());

        if ($form->isValid()){
            $em = $this->getDoctrine()->getManager();
            $em->persist($user);
            $em->flush();

            return new JsonResponse($this->serialize($user), Response::HTTP_CREATED, [], true);
        } else{
            $errors = $this->getErrorsFromForm($form);
            $data = [
                'type' => 'validation_error',
                'title' => 'There was a validation error',
                'errors' => $errors,
                'value' => $user
            ];
            return new JsonResponse($this->serialize($data), Response::HTTP_BAD_REQUEST, [], true);
        }
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     *
     * @Route("/users/{id}", name="users_edit")
     *
     * @Method("PUT")
     */
    public function updateUserAction(Request $request){
        $em = $this->getDoctrine()->getManager();
        $user = $em->getRepository(User::class)->findOneById($request->get('id'));

        if (empty($user)){
            return new JsonResponse(array(
                'message' => 'User not found',
                'id' => $request->get('id')
            ), Response::HTTP_NOT_FOUND);
        }

        $form = $this->createForm(UserType::class, $user, array('method' => 'PUT'));

        $form->submit($request->request->all());

        if ($form->isValid()){
            $em->flush();
            return new JsonResponse($this->serialize($user), Response::HTTP_CREATED, [], true);
        } else{
            $errors = $this->getErrorsFromForm($form);
            $data = [
                'type' => 'validation_error',
                'title' => 'There was a validation error',
                'errors' => $errors,
                'value' => $user
            ];
            return new JsonResponse($this->serialize($data), Response::HTTP_BAD_REQUEST, [], true);
        }
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     *
     * @Route("/users/{id}", name="users_delete")
     *
     * @Method("DELETE")
     */
    public function deleteUserAction(Request $request){

        $em = $this->getDoctrine()->getManager();
        $user = $em->getRepository(User::class)->findOneById($request->get('id'));

        if ($user){
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
}
