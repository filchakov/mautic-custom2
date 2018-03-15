<?php

namespace App\Http\Controllers;

use App\Project;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

use Mautic\Auth\ApiAuth;
use Mautic\MauticApi;

class WebhookController extends Controller
{
    /**
     * @api {post} /webhooks/lead Create new contact
     * @apiName new_lead
     * @apiGroup User
     * @apiVersion 1.0.0
     *
     * @apiParam {String="http://example.com","example.com"} project_url Project's URL (Example: http://dev.webscribble.com or dev.webscribble.com)
     * @apiParam {String} firstname First name
     * @apiParam {String} lastname Last name
     * @apiParam {String} email Email address (Example: lead@company.com)
     * @apiParam {String="job_seeker","member"} {type_account=job_seeker} type_account Type account
     *
     * @apiParam {String} [phone] Phone number
     * @apiParam {String} [website] Website
     * @apiParam {String} [city] City
     * @apiParam {String} [address1] Address 1
     * @apiParam {String} [address2] Address 2
     * @apiParam {String} [state] State
     * @apiParam {String} [country] Country
     * @apiParam {String} [zipcode] Zip code
     *
     * @apiSuccess {Boolean=true} status Status request
     * @apiSuccess {Object} data Lead profile information
     * @apiSuccess {Number} data.id Lead's ID
     * @apiSuccess {String="job_seeker","member"} data.type_account Type account
     * @apiSuccess {String} data.firstname First name
     * @apiSuccess {String} data.lastname Last name
     * @apiSuccess {String} data.email Email address
     * @apiSuccess {String} [data.phone] Phone
     * @apiSuccess {String} [data.website] Website
     * @apiSuccess {String} [data.city] City
     * @apiSuccess {String} [data.address1] Address 1
     * @apiSuccess {String} [data.address2] Address 2
     * @apiSuccess {String} [data.state] State
     * @apiSuccess {String} [data.country] Country
     * @apiSuccess {String} [data.zipcode] Zip code
     *
     * @apiError ProjectNotFound The project URL was not found in projects database
     * @apiErrorExample Error-Response:
     *
     *  HTTP/1.1 404 Not Found
     *  {
     *      "status": false,
     *      "data": {
     *          "project_url": "URL does not exist in a database"
     *      }
     *  }
     *
     *
     * @apiError EmptyField An error that appears while passing a request with an empty  field which has status "required"
     * @apiErrorExample Error-Response:
     *
     *  HTTP/1.1 400 Bad Request
     *  {
     *      "status": false,
     *      "data": {
     *          "FIELD_NAME": "DESCRIPTION"
     *      }
     *  }
     *
     *
     */

    public function create(Request $request){

        $errors = [];

        $code = 400;

        $project_url = $request->get('project_url', false);

        if(empty($project_url)){
            $errors['project_url'] = 'Missing field';
        } else {
            $project = Project::where('url', 'like', '%'.$project_url.'%')->first();
            if(empty($project)) {
                $errors['project_url'] = 'URL does not exist in a database';
                $code = 404;
            }
        }

        if(in_array($request->get('type_account', false), ['job_seeker', 'member'])){
            $type_account = $request->get('type_account');
        } else {
            $type_account = 'job_seeker';
        }

        $email = $request->get('email', false);

        if(empty($email)) {
            $errors['project_url'] = 'Missing field';
        }

        if(!filter_var($email, FILTER_VALIDATE_EMAIL)){
            $errors['email'] = 'Is not valid email';
        }

        if(!empty($errors)){
            return \response()->json([
                'status' => false,
                'data' => $errors
            ], $code);

        } else {
            $settings = ['userName'   => env('MAUTIC_LOGIN'), 'password'   => env('MAUTIC_PASSWORD'), 'debug' => true];

            $initAuth = new ApiAuth();

            $auth = $initAuth->newAuth($settings, 'BasicAuth');

            $api = new MauticApi();

            $contactApi = $api->newApi('contacts', $auth, env('MAUTIC_URL'));

            $contact = $contactApi->create(
                array_merge([
                    'owner' => $project->mautic_id,
                    'tags' => [$type_account]
                ], $request->toArray())
            );

            if(!empty($contact['contact'])){

                $result_data = [
                    'id' => $contact['contact']['id'],
                    'type_account' => $type_account,
                ];

                foreach ($request->toArray() as $name => $value) {
                    if(isset($contact['contact']['fields']['core'][$name])){
                        $result_data[$name] = $contact['contact']['fields']['core'][$name]['value'];
                    }
                }

                return response()->json([
                    'status' => true,
                    'data' => $result_data
                ], 200);
            }
        }
    }
}
