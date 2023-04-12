<?php

namespace App\Http\Controllers;

use App\Models\Record;
use App\Models\Release;
use App\Models\Studentinfo;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Validator;

class Dashboardcontroller extends Controller
{

    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $n_students = Studentinfo::count();
        $n_students_acads = Record::count();

        $release = Release::all()->values()->reverse()->take(5);

        $data = [
            'student' => $n_students,
            'acads' => $n_students_acads,
            'releases' => $release
        ];
        return view('partials.dashboard_content', compact('data'));
    }

    public function dataManagement()
    {
        return view('partials.data_management');
    }

    public function subjectManage()
    {
        return view('partials.manage_subject');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        // $request->session()->invalidate();
        // $request->session()->regenerateToken();

        return response()->json(route('login'), 200);
    }

    public function settings()
    {
        $user = Auth::user();
        return view('settings.index', compact('user'));
    }

    public function basicInfo(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'firstname' => 'required|regex:/^[a-zA-Z ]+$/u',
            'lastname' => 'required|regex:/^[a-zA-Z ]+$/u',
            'email' => 'required|email',
        ]);

        if (!$validator->passes()) {
            return response()->json(['status' => 0, 'error' => $validator->errors()->toArray()]);
        }

        User::where('id', Auth::user()->id)->update([
            'firstname' => $request->firstname,
            'lastname' => $request->lastname,
            'email' => $request->email,
        ]);

        return response()->json(['status' => 200, 'msg' => 'Changes has been saved!.']);
    }


    public function passwordInfo(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'old_password' => 'required|min:8',
            'password' => 'required|min:8|confirmed'
        ]);

        if (!$validator->passes()) {
            return response()->json(['status' => 0, 'error' => $validator->errors()->toArray()]);
        }

        if (!Hash::check($request->old_password, Auth::user()->password)) {
            return response()->json(['status' => 401, 'error' => 'Incorrect old password!']);
        }

        User::where('id', Auth::user()->id)->update([
            'password' => Hash::make($request->password)
        ]);

        return response()->json(['status' => 200, 'msg' => 'Changes has been saved!.']);
    }

    public function resetInfo(Request $request)
    {

        $validator = Validator::make(
            $request->all(),
            [
                'old_password_q' => 'required|min:8',
                'security_question' => 'required',
                'security_answer' => 'required'
            ],
            [
                'old_password_q.required' => 'Old password is required!'
            ]
        );

        if (!$validator->passes()) {
            return response()->json(['status' => 0, 'error' => $validator->errors()->toArray()]);
        }

        if (!Hash::check($request->old_password_q, Auth::user()->password)) {
            return response()->json(['status' => 402, 'error' => 'Incorrect old password!']);
        }

        User::where('id', Auth::user()->id)->update([
            'security_question' => $request->security_question,
            'security_answer' => $request->security_answer
        ]);

        return response()->json(['status' => 200, 'msg' => 'Changes has been saved!.']);
    }
}
