@extends('layouts.app')

@section('content')
    <div class="container mb-5 mt-5">
        <div class="row text-center">
            <div class="col-12">
                <h1 class="text-uppercase">Exporter app</h1>
            </div>
        </div>
    </div>

    <div class="container">
        <div class="row justify-content-center mb-5">
            <div class="col-8">
                <form method="POST" id="post-form" action="{{ route('post.data') }}">
                    @csrf
                    <div class="form-group row mb-3">
                        <h4>Choose one or more SQL files</h4>
                        <span class="text-muted mb-3">NOTE: If multiple files selected, they will be merged into one.</span>
                        <div class="btn-group flex-wrap" role="group" aria-label="Basic checkbox toggle button group">
                            @foreach($sqlFiles as $key => $file)
                                <input type="checkbox" name="sql_file[]" class="btn-check" id="btncheck1_{{ $key }}"
                                       @if($key === 0) checked @endif value="{{ $file }}" autocomplete="off">
                                <label class="btn btn-outline-primary mb-1" for="btncheck1_{{ $key }}">{{ $file }}</label>
                            @endforeach
                        </div>
                        @error('sql_file')<small class="text-danger">{{ $message }}</small>@enderror
                        <h4 class="mb-3 mt-5">Choose which type of export you need</h4>
                        <div class="btn-group flex-wrap" role="group" aria-label="Basic radio toggle button group">
                            <input type="radio" class="btn-check" name="typeExport" id="type_txt" value="txtExport" autocomplete="off" checked>
                            <label class="btn btn-outline-primary" for="type_txt">Export to TXT</label>
                            <input type="radio" class="btn-check" name="typeExport" id="type_xml" value="xmlExport" autocomplete="off">
                            <label class="btn btn-outline-primary" for="type_xml">Export to XML</label>
                            <input type="radio" class="btn-check" name="typeExport" id="type_csv" value="csvExport" autocomplete="off">
                            <label class="btn btn-outline-primary" for="type_csv">Export to CSV</label>
                        </div>
                        @error('typeExport')<small class="text-danger">{{ $message }}</small>@enderror
                    </div>
                    <div class="form-group row mt-4">
                        <div class="col">
                            <button type="submit" class="btn btn-primary btn-shadow-primary text-uppercase">Generate file</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <div class="row justify-content-center">
            <div class="col-8">
                <table class="table">
                    <thead>
                    <tr>
                        <th scope="col">Filename</th>
                        <th scope="col class=" class="text-end">Download</th>
                    </tr>
                    </thead>
                    <tbody>
                        @if(count($files) === 0)
                            <tr>
                                <td>There are no generated files</td>
                                <td class="text-end"></td>
                            </tr>
                        @else
                            @foreach(array_reverse($files) as $file)
                                <tr>
                                    <td>{{ $file }}</td>
                                    <td class="text-end"><form action="{{ route('file.download', $file) }}" class="d-inline-block mr-2" method="GET">
                                            @csrf
                                            @method('GET')
                                            <button type="submit" class="btn btn-primary text-uppercase">Download</button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        @endif
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
