<?php

namespace App\Http\Controllers;

use App\Models\ContactMessage;
use App\Models\Department;
use App\Models\Doctor;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class PublicSiteController extends Controller
{
    public function home(): View
    {
        return view('public.home', [
            'departments' => Department::query()->withCount('doctors')->orderBy('name')->limit(3)->get(),
            'doctors' => Doctor::query()->with(['department', 'user'])->orderBy('id')->limit(3)->get(),
        ]);
    }

    public function about(): View
    {
        return view('public.about');
    }

    public function services(): View
    {
        return view('public.services');
    }

    public function departments(): View
    {
        return view('public.departments', [
            'departments' => Department::query()->withCount('doctors')->orderBy('name')->get(),
        ]);
    }

    public function doctors(): View
    {
        return view('public.doctors', [
            'doctors' => Doctor::query()->with(['department', 'user'])->orderBy('id')->get(),
        ]);
    }

    public function showDoctor(Doctor $doctor): View
    {
        $doctor->load(['department', 'schedules', 'user']);

        return view('public.doctors.show', compact('doctor'));
    }

    public function contact(): View
    {
        return view('public.contact');
    }

    public function storeContact(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:30'],
            'subject' => ['nullable', 'string', 'max:255'],
            'message' => ['required', 'string', 'max:5000'],
        ]);

        ContactMessage::create($validated);

        return redirect()->route('contact')->with('status', 'Thank you. Our care team will be in touch soon.');
    }
}
