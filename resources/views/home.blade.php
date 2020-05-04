@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-md-8 col-md-offset-2">
        <form action="/delete" method="post" enctype="multipart/form-data" class="uploaded-form">
            <div class="panel panel-default">
                
                <div class="panel-heading">My Files <button class="btn btn-default add-folder">Add Folder</button></div>


                <div class="panel-heading-head">
                <a class="return-btn btn btn-default"><-</a>
                @if($opened === false)
                <p>Root</p>
                @else
                @foreach ($folders as $folder)
                    <p>Root/{{$prevPath}}</p>
                   
                @endforeach
                @endif

                </div>
                <div class="panel-body">

                @forelse ($folders as $folder)
                    @if($folder->folder_id === null && $opened === false)
                    
                    <li ><input type="checkbox" class="check-folder" name="check-folder" data-url='{{$folder->name}}' data-id='{{$folder->id}}'><a data-name-folder="{{$folder->name}}" data-id-folder="{{$folder->id}}" href="uploads/{{$folder->name}}" class="folder"><img class="folder" src="/css/img/folder.png" alt="logo d'un dossier" width="15px" height="15px">{{$folder->name}}</a><span class="download"><a  href="/uploads/{{$folder->name}}" download="{{$folder->name}}" class="folder-down" data-name-folder="{{$folder->name}}">Download</a></span></li>

                    @else
                    @foreach($subFolders as $sub)
                    <li ><input type="checkbox" class="check-folder" name="check-folder" data-url='{{$sub->name}}' data-id='{{$sub->id}}'><a data-name-folder="{{$sub->name}}" data-id-folder="{{$sub->id}}" href="uploads/{{$sub->name}}" class="folder"><img class="folder" src="/css/img/folder.png" alt="logo d'un dossier" width="15px" height="15px">{{$sub->name}}</a><span class="download"><a href="/uploads/{{$sub->name}}" download="{{$sub->name}}" data-name-folder="{{$sub->name}}" class="folder-down">Download</a></span></li>
                    @endforeach
                    @endif


                @empty
                @endforelse
                
                @forelse ($files as $file)
                    <li><input type="checkbox" class="check" name="check" data-url='{{$file->url}}' data-id='{{$file->id}}'><span class="file"><a href="/uploads/{{$file->url}}">{{ $file->name }}</a></span> <span class="size">size : {{$file->size}} octet</span> <span class="download"><a href="/uploads/{{$file->url}}" download="{{$file->name}}" >Download</a></span></li>
                @empty
                    <p>No files on your server</p>
                @endforelse
                    
                 
                </div>
            </div>
            <button type="submit" class="btn btn-danger delete">Delete</button>
             {{ csrf_field() }}
        </form>
        </div>
    </div>
</div>

    
@endsection
