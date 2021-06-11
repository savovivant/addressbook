<?php

namespace App\Http\Controllers\API;

use App\Models\Agency;
use App\Models\Contact;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Http\Response;
use League\CommonMark\Inline\Element\Image;


class ContactController extends BaseController
{

    public function index(Request $request)
    {
        $user = \Auth::guard('api')->user();
        if (!$user) {
            return 'Not authenticated.';
        }

        $contacts = Contact::with('agency')->get();

        return [
            'status' => "success",
            'data'   => $contacts,
        ];
    }

    public function show($id)
    {
        $user = \Auth::guard('api')->user();
        if (!$user) {
            return 'Not authenticated.';
        }

        $contact = Contact::with('professions')->find($id);
        if (!$contact) {
            return 'Contact Not found.';
        }

        return [
            'status' => "success",
            'data'   => $contact,
        ];
    }

    public function create(Request $request)
    {
        $data = $request->validate([
            'first_name' => 'required',
            'last_name' => 'required',
            'agency_id' => 'required',
            'phone' => 'required',
            'email' => 'required',
            'web' => 'required',
            'avatar' => 'required',
        ]);

        $image = $request->avatar;
        $image = str_replace('data:image/png;base64,', '', $image);
        $image = str_replace(' ', '+', $image);
        $imageName = rand(10,22).'.'.'png';
        \File::put(public_path('images'). '/' . $imageName, base64_decode($image));
        $data['avatar'] = $imageName;
        $professions = $request->professions;

        $contact = Contact::create($data);
        $contact->professions()->attach($professions);
        if (is_null($contact)) {
            return response(['status' => "failed"]);
        }

        return [
            'status' => "success",
            'data'   => $contact,
        ];
    }

    public function update(Request $request, $id)
    {
        $agency = Agency::find($id);
        if (!$agency) {
            return 'Not agency found.';
        }

        $data = $request->validate([
            'first_name' => 'required',
            'last_name' => 'required',
            'agency_id' => 'required',
            'phone' => 'required',
            'email' => 'required',
            'web' => 'required',
            'avatar' => 'required',
        ]);

        $agency->update($data);

        return [
            'status'  => "success",
            'data'    => $agency,
            'Message' => "Successfully updated",
        ];
    }

    public function destroy($id)
    {
        $contact = Contact::findOrFail($id);
        $contact->delete();

        return [
            'status'  => "success",
            'Message' => "Successfully deleted",
        ];
    }
}
