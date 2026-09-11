<footer class="footer pt-5 pb-4 mt-0">
    <div class="container">
        <div class="row g-4 pb-4">
            <div class="col-lg-4"><div class="d-flex align-items-center text-white fw-bold fs-4 mb-3"><span class="brand-mark">+</span>MediCare</div><p class="mb-0">Thoughtful, evidence-based care for our community—delivered with expertise and warmth.</p></div>
            <div class="col-6 col-lg-2"><h2 class="footer-heading">Explore</h2><ul class="list-unstyled small lh-lg mb-0"><li><a href="{{ route('about') }}">About us</a></li><li><a href="{{ route('services') }}">Services</a></li><li><a href="{{ route('departments.index') }}">Departments</a></li></ul></div>
            <div class="col-6 col-lg-2"><h2 class="footer-heading">Care team</h2><ul class="list-unstyled small lh-lg mb-0"><li><a href="{{ route('doctors.index') }}">Our doctors</a></li><li><a href="{{ route('contact') }}">Contact us</a></li><li><a href="{{ route('login') }}">Patient sign in</a></li></ul></div>
            <div class="col-lg-4"><h2 class="footer-heading">Visit MediCare</h2><p class="small mb-1">100 Healthway Drive<br>Springfield, USA</p><p class="small mb-0">(555) 010-2026<br>care@medicare.test</p></div>
        </div>
        <div class="border-top border-secondary pt-4 small d-flex flex-column flex-md-row justify-content-between gap-2"><span>© {{ now()->year }} MediCare Hospital. All rights reserved.</span><span>For emergencies, call your local emergency number.</span></div>
    </div>
</footer>
