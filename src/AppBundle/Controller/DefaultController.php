<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Facebook\Facebook;

class DefaultController extends Controller
{

    /**
     * @Route("/", name="homepage")
     */
    public function indexAction(Request $request)
    {
        $facebook = $this->getParameter('facebook');

        $fb = new Facebook($facebook);

        $helper      = $fb->getRedirectLoginHelper();
        $permissions = ['email'];
        $fbLoginUrl  = $helper->getLoginUrl('http://link.com/app_dev.php/fbcheck', $permissions);

        // replace this example code with whatever you need
        return $this->render('default/index.html.twig', [
                    'fb_login_url' => $fbLoginUrl,
                    'base_dir'     => realpath($this->getParameter('kernel.root_dir').'/..').DIRECTORY_SEPARATOR,
        ]);
    }

    /**
     * @Route("/fb-login", name="fb-login")
     */
    public function fbLoginAction(Request $request)
    {
        $facebook = $this->getParameter('facebook');

        $fb = new Facebook($facebook);

        $helper = $fb->getRedirectLoginHelper(); // to perform operation after redirection
        try {
            $accessToken = $helper->getAccessToken(); // to fetch access token
        } catch (Facebook\Exceptions\FacebookResponseException $e) {
            // When facebook server returns error
            echo 'Graph returned an error: '.$e->getMessage();
            exit;
        } catch (Facebook\Exceptions\FacebookSDKException $e) {
            // when issue with the fetching access token
            echo 'Facebook SDK returned an error: '.$e->getMessage();
            exit;
        }
        if (!isset($accessToken)) {// checks whether access token is in there or not
            if ($helper->getError()) {
                header('HTTP/1.0 401 Unauthorized');
                echo "Error: ".$helper->getError()."\n";
                echo "Error Code: ".$helper->getErrorCode()."\n";
                echo "Error Reason: ".$helper->getErrorReason()."\n";
                echo "Error Description: ".$helper->getErrorDescription()."\n";
            } else {
                header('HTTP/1.0 400 Bad Request');
                echo 'Bad request';
            }
            exit;
        }

        try {
            // to get required fields using access token
            $response = $fb->get('/me?fields=id,name', $accessToken->getValue());
        } catch (Facebook\Exceptions\FacebookResponseException $e) {// throws an error if invalid fields are specified
            echo 'Graph returned an error: '.$e->getMessage();
            exit;
        } catch (Facebook\Exceptions\FacebookSDKException $e) {
            echo 'Facebook SDK returned an error: '.$e->getMessage();
            exit;
        }

        $user = $response->getGraphUser(); // to get user details

        echo 'Name: '.$user['name']; die;
    }

    /**
     * @Route("/admin/", name="admin")
     */
    public function adminAction(Request $request)
    {
        $user = $this->getUser();

        // replace this example code with whatever you need
        return $this->render('AppBundle::admin.html.twig');
    }
}
