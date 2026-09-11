@props(['action', 'description'])
<details class="mt-2">
    <summary class="text-danger">Delete</summary>
    <p class="small text-secondary my-2">{{ $description }}</p>
    <form method="POST" action="{{ $action }}">
        @csrf
        @method('DELETE')
        <button class="btn btn-danger btn-sm" type="submit">Confirm deletion</button>
    </form>
</details>
