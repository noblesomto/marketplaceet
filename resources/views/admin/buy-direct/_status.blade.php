@if(session('status'))
    <div class="alert alert-{{ session('status')['type'] ?? 'info' }} alert-dismissible fade show">
        {{ session('status')['text'] ?? 'Done' }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif
@if($errors->any())
    <div class="alert alert-danger alert-dismissible fade show">
        {{ $errors->first() }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif
