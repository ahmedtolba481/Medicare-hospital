@extends('layouts.public')

@section('content')
    <x-public.page-header title="Contact MediCare" subtitle="Our team is here to help you find the right next step." />
    <section class="section-padding">
        <div class="container">
            <div class="row g-5">
                <div class="col-lg-5">
                    <p class="eyebrow mb-2">Get in touch</p>
                    <h2 class="h3 mb-3">Let’s start with a conversation.</h2>
                    <p class="muted-copy">Send a message and our care team will respond during regular business hours. For emergencies, call your local emergency number.</p>
                    <div class="contact-card card p-4 mt-4 h-auto">
                        <p class="mb-3"><strong>Visit us</strong><br><span class="text-secondary">100 Healthway Drive, Springfield, USA</span></p>
                        <p class="mb-3"><strong>Call us</strong><br><a href="tel:+15550102026">(555) 010-2026</a></p>
                        <p class="mb-3"><strong>Email us</strong><br><a href="mailto:care@medicare.test">care@medicare.test</a></p>
                        <p class="mb-0"><strong>Hours</strong><br><span class="text-secondary">Monday–Friday, 8:00 AM–6:00 PM</span></p>
                    </div>
                </div>
                <div class="col-lg-7">
                    <div class="card contact-card p-4 p-md-5">
                        @if ($errors->any())
                            <div class="alert alert-danger" role="alert">Please correct the highlighted fields and send your message again.</div>
                        @endif
                        <form method="POST" action="{{ route('contact.store') }}" novalidate>
                            @csrf
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label" for="name">Name</label>
                                    <input id="name" class="form-control @error('name') is-invalid @enderror" name="name" value="{{ is_scalar(old('name')) ? old('name') : '' }}" autocomplete="name" maxlength="255" required @error('name') aria-invalid="true" aria-describedby="name-error" @enderror>
                                    @error('name')<div id="name-error" class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label" for="email">Email</label>
                                    <input id="email" class="form-control @error('email') is-invalid @enderror" name="email" type="email" value="{{ is_scalar(old('email')) ? old('email') : '' }}" autocomplete="email" maxlength="255" required @error('email') aria-invalid="true" aria-describedby="email-error" @enderror>
                                    @error('email')<div id="email-error" class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label" for="phone">Phone <span class="text-secondary">(optional)</span></label>
                                    <input id="phone" class="form-control @error('phone') is-invalid @enderror" name="phone" type="tel" value="{{ is_scalar(old('phone')) ? old('phone') : '' }}" autocomplete="tel" maxlength="30" @error('phone') aria-invalid="true" aria-describedby="phone-error" @enderror>
                                    @error('phone')<div id="phone-error" class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label" for="subject">Subject <span class="text-secondary">(optional)</span></label>
                                    <input id="subject" class="form-control @error('subject') is-invalid @enderror" name="subject" value="{{ is_scalar(old('subject')) ? old('subject') : '' }}" maxlength="255" @error('subject') aria-invalid="true" aria-describedby="subject-error" @enderror>
                                    @error('subject')<div id="subject-error" class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-12">
                                    <label class="form-label" for="message">How can we help?</label>
                                    <textarea id="message" class="form-control @error('message') is-invalid @enderror" name="message" rows="5" maxlength="5000" required @error('message') aria-invalid="true" aria-describedby="message-error" @enderror>{{ is_scalar(old('message')) ? old('message') : '' }}</textarea>
                                    @error('message')<div id="message-error" class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-12"><button class="btn btn-primary px-4" type="submit">Send message</button></div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
