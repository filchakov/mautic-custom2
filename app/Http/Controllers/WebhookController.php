<?php

namespace App\Http\Controllers;

use App\Jobs\CreateContactsOnMautic;

use App\Project;
use Illuminate\Http\Request;

class WebhookController extends Controller
{
    /**
     * @api {post} /webhooks/lead Create new contacts
     * @apiName new_leads
     * @apiGroup User
     * @apiVersion 1.0.0
     *
     * @apiExample {php} PHP Example:
        <?php

            $data = array(
                [
                    "firstname" => "Sam",
                    "lastname" => "Uncle",
                    "tags" => "job_seeker,member",
                    "phone" => "18005005050",
                    "email" => "sam.unlce@example.com",
                    "project_url" => "http://dev.webscribble.com"
                ], [
                    "firstname" => "Sam2",
                    "lastname" => "Uncle2",
                    "tags" => "job_seeker,member",
                    "phone" => "18005005250",
                    "email" => "sam.unlce2@example.com",
                    "project_url" => "http://dev.webscribble.com"
                ],
            );

            $data_string = json_encode($data);

            $ch = curl_init('https://email-builder.hiretrail.com/webhooks/lead');
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                'Content-Type: application/json',
                'Content-Length: ' . strlen($data_string))
            );

            $result = curl_exec($ch);
     *
     * @apiParam {String="http://example.com","example.com"} project_url Project's URL (Example: http://dev.webscribble.com or dev.webscribble.com)
     * @apiParam {String} firstname First name
     * @apiParam {String} lastname Last name
     * @apiParam {String} email Email address (Example: lead@company.com)
     * @apiParam {String} [tags] Tags (field supports multiple types, with delimiter ",". Example "job_seeker,member")
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
     * @apiSuccess {String} [data.tags] Tags
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
     * @apiErrorExample 404-Error-Response:
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
     * @apiErrorExample 400-Error-Response:
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

        try {

            $result = [];
            foreach ($request->toArray() as $contact){

                $errors = [];

                //Checking email field
                if(empty($contact['email'])) {
                    $errors['email'] = 'Missing field';
                } elseif (!filter_var($contact['email'], FILTER_VALIDATE_EMAIL)){
                    $errors['email'] = 'Is not valid email';
                }

                //Checking project_url field
                if(empty($contact['project_url'])){
                    $errors['project_url'] = 'Missing field';
                } elseif(filter_var($contact['project_url'], FILTER_VALIDATE_URL) != $contact['project_url']) {
                    $errors['project_url'] = 'Is not valid URL of project';
                } else {

                    $url = parse_url($contact['project_url']);

                    $project = Project::where('url', 'like', '%'.$url['host'] .'%')->first();

                    if(empty($project)) {
                        $errors['project_url'] = 'URL does not exist in a database';
                    }
                }

                if (empty($errors)){
                    $result[] = ["status" => true, "fields" => []];
                    CreateContactsOnMautic::dispatch($contact)->onQueue(env('APP_ENV').'-CreateContactsOnMautic');
                } else {
                    $result[] = ["status" => false, "fields" => $errors];
                }
            }

            return response()->json([
                'status' => true,
                'data' => $result
            ], 200);

        } catch (\Exception $e){
            return response()->json([
                'status' => true,
                'data' => [
                    'message' => $e->getMessage()
                ]
            ], 500);
        }

    }
}
