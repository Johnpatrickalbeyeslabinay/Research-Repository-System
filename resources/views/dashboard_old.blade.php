@include('layouts.instructor_layout', ['title' => 'Instructor Dashboard'])

<script src="{{ asset('js/folder.js') }}"></script>

<div class="smart-search">  
    <div class="input-group mb-3">
        <input type="text" class="form-control" placeholder="Search for files, folders, or tags..." id="searchInput">
        <button class="btn btn-outline-secondary" type="button" id="searchButton">
            <i class="fa-solid fa-magnifying-glass"></i>
        </button>
    </div>

    <div id="searchResults" class="list-group"></div>

</div>
      




<nav class="menu-links d-flex justify-content-start gap-4 mb-4">
  <div class="menu-item">
        <a href="#" class="menu-link" onclick="toggleFolders(event)">
        <i class="fa-solid fa-folder-open me-2"></i> Folders
        </a>

        <div class="folder-container" id="folderMenu">
        @foreach(($allowedFolders ?? []) as $folderName)
            <a class="folder" href="{{ route('dashboard', ['folder' => $folderName]) }}">
            <i class="fa-solid fa-folder me-2"></i>
            <span>{{ $folderName }}</span>
            </a>
        @endforeach
        </div>
       
    </div>

    <div>
        <a href="{{ route('dashboard') }}" class="menu-link">
        <i class="fa-solid fa-file me-2"></i> All Files
        </a>
    </div>

    <div>
        
    </div>

</nav>




<nav>
  <div class="folder"></div>
</nav>

<div class="view-recent-files d-flex justify-content-start mb-4">
    <div class="recent-files-card">
       <h1>Recent Files</h1>
        <ul class="list-group">
          <li class="list-group-item">
              <i class="fa-solid fa-clock me-2 text-primary"></i> Lab Report 1 - Fundamental Fisheries
          </li>
          <li class="list-group-item">
              <i class="fa-solid fa-clock me-2 text-primary"></i> Lab Report 2 - General Zoology
          </li>
          <li class="list-group-item">
              <i class="fa-solid fa-clock me-2 text-primary"></i> Lab Report 3 - Organic Analytical Chemistry
    </div>

</div>


<div class="subject-folder">
    <div class="folder-header">
        <button type="button" class="btn btn-outline-secondary" id="showUploadFormButton">
            <i class="fa-solid fa-upload me-2"></i>Upload File
        </button>

        <form action="{{ route('userfile.store') }}" method="POST" enctype="multipart/form-data" class="d-flex flex-column gap-2 mt-3 d-none" id="uploadForm">
            @csrf
            <h5>Upload File</h5>
            <!-- Title -->
            <input type="text" name="title" class="form-control" placeholder="File Title" required>

            <!-- Type -->
            <div class="mb-3">
            <input list="typelist" name="type" class="form-control" placeholder="Type (e.g.,)" envrequired>
                <datalist id="typelist" name="type" >
                    <option value="Water Quality"></option>
                    <option value="Fish Growth"></option>
                    <option value="Breeding"></option>
                    <option value="Nutrition"></option>
                    <option value="Disease Analysis"></option>
                    <option value="Environmental"></option>
                </datalist>
            </div>

            <!-- Instructor -->
            <input type="text" name="instructor" class="form-control" placeholder="Instructor Name" required>

            <!-- File -->
            <input type="file" name="file" class="form-control" required>

            <!-- Folder -->
            <select name="folder" class="form-control" required>
                <option value="" disabled selected>Select Folder</option>
                @foreach(($allowedFolders ?? []) as $folderName)
                  <option value="{{ $folderName }}">{{ $folderName }}</option>
                @endforeach
            </select>

            <!-- Buttons -->
            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-outline-secondary">Submit</button>
                <button type="button" class="btn btn-outline-dark" id="hideUploadFormButton">Cancel</button>
            </div>
        </form>
    </div>
</div>


<div class="lab-files">

<div class="lab-files mt-5">
    <h2>
      Uploaded Lab Files
      @if(!empty($selectedFolder))
        - {{ $selectedFolder }}
      @endif
    </h2>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger">
            {{ $errors->first() }}
        </div>
    @endif

    <div class="uploaded-files-grid">
    @forelse(($files ?? collect()) as $file)
        <div class="card p-3">
            <h5>{{ $file->title }}</h5>

            <p><strong>Folder:</strong> {{ $file->folder }}</p>

            <p class="text-primary">{{ $file->auto_tag }}</p>

            <p>
                <small class="text-muted">
                    Uploaded by:
                    {{ trim(($file->user->first_name ?? '') . ' ' . ($file->user->last_name ?? '')) ?: 'Unknown' }}
                    <br>
                    Date: {{ optional($file->created_at)->format('F d, Y h:i A') ?? 'N/A' }}
                </small>
            </p>

            <a href="{{ asset('storage/' . $file->file_path) }}" 
               target="_blank" 
               class="btn btn-sm btn-dark">
                View File
            </a>

            @if((int) Auth::id() === (int) $file->user_id)
                <details class="mt-3">
                    <summary>Edit / Delete</summary>

                    <form action="{{ route('userfile.update', $file) }}" method="POST" enctype="multipart/form-data" class="mt-2 d-flex gap-2">
                        @csrf
                        @method('PUT')
                        <input type="text" name="title" class="form-control" value="{{ $file->title }}" required>
                        <select name="folder" class="form-control" required>
                            @foreach(($allowedFolders ?? []) as $folderName)
                                <option value="{{ $folderName }}" {{ $file->folder === $folderName ? 'selected' : '' }}>
                                    {{ $folderName }}
                                </option>
                            @endforeach
                        </select>
                        <input type="file" name="file" class="form-control">
                        <button type="submit" class="btn btn-sm btn-outline-primary">Save</button>
                    </form>

                    <form action="{{ route('userfile.destroy', $file) }}" method="POST" class="mt-2" onsubmit="return confirm('Delete this file?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-sm btn-outline-danger">Delete</button>
                    </form>
                </details>
            @endif
        </div>
    @empty
        <p class="text-muted">No uploaded files yet.</p>
    @endforelse
    </div>
</div>

<br><br>
 
@include('includes.footer')

<script>
document.addEventListener('DOMContentLoaded', function () {
    const showButton = document.getElementById('showUploadFormButton');
    const hideButton = document.getElementById('hideUploadFormButton');
    const uploadForm = document.getElementById('uploadForm');

    if (!showButton || !hideButton || !uploadForm) {
        return;
    }

    showButton.addEventListener('click', function () {
        uploadForm.classList.remove('d-none');
        showButton.classList.add('d-none');
    });

    hideButton.addEventListener('click', function () {
        uploadForm.classList.add('d-none');
        showButton.classList.remove('d-none');
        uploadForm.reset();
    });
});
</script>
