<?php

namespace App\Http\Controllers;

use App\Models\Job;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class JobController extends Controller
{
    //Show all jobs
    public function index()
    {
        return view('jobs.index', [
            'jobs' => Job::latest()->filter(request(['tag', 'search']))->paginate(4)
        ]);
    }

    //Show single job
    public function show(Job $job)
    {

        return view('jobs.show', [
            'job' => $job
        ]);
    }

    //Show Create Form
    public function create()
    {
        return view('jobs.create');
    }

    //Store job data
    public function store(Request $request)
    {
        $formFields = $request->validate([
            'title' => 'required',
            'company' => ['required', Rule::unique('jobs', 'company')],
            'location' => 'required',
            'website' => 'required',
            'email' => ['required', 'email'],
            'tags' => 'required',
            'description' => 'required'
        ]);

        if ($request->hasFile('logo')) {
            $formFields['logo'] = $request->file('logo')->store('logos', 'public');
        }

        $formFields['user_id'] = auth()->id();

        Job::create($formFields);

        return redirect('/')->with('message', 'Job created successfully!');
    }

    //Show Edit Form
    public function edit(Job $job)
    {

        return view('jobs.edit', ['job' => $job]);
    }

    //Update job data
    public function update(Request $request, Job $job)
    {
        //Make sure logged in uset is the owner 
        if($job->user_id != auth()->id()) {
            abort(403, 'Unauthorized Action!');
        }

        $formFields = $request->validate([
            'title' => 'required',
            'company' => ['required'],
            'location' => 'required',
            'website' => 'required',
            'email' => ['required', 'email'],
            'tags' => 'required',
            'description' => 'required'
        ]);

        if ($request->hasFile('logo')) {
            $formFields['logo'] = $request->file('logo')->store('logos', 'public');
        }

        $formFields['user_id'] = auth()->id();

        $job->update($formFields);

        return back()->with('message', 'Job updated successfully!');
    }

    //Delete Job
    public function destroy(Job $job)
    {
        //Make sure logged in uset is the owner 
        if($job->user_id != auth()->id()) {
            abort(403, 'Unauthorized Action!');
        }
        
        $job->delete();
        return redirect('/')->with('message', 'Job deleted successfully!');
    }
    
    //Manage Jobs
    public function manage() {
        return view('jobs.manage', ['jobs' => auth()->user()->jobs()->get()]);
    }
}
