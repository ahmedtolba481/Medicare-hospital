@props(['patient'])

<dl class="row mb-0 text-break">
    <dt class="col-sm-4">Name</dt><dd class="col-sm-8">{{ $patient->name }}</dd>
    <dt class="col-sm-4">Email</dt><dd class="col-sm-8">{{ $patient->email }}</dd>
    <dt class="col-sm-4">Phone</dt><dd class="col-sm-8">{{ $patient->phone ?: 'Not provided' }}</dd>
    <dt class="col-sm-4">Date of birth</dt><dd class="col-sm-8">{{ $patient->date_of_birth?->format('M j, Y') ?? 'Not provided' }}</dd>
    <dt class="col-sm-4">Address</dt><dd class="col-sm-8">{{ $patient->address ?: 'Not provided' }}</dd>
</dl>
