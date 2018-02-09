@extends('layouts.app')

@section('content')
<thread-view inline-template :init-replies-count="{{ $thread->replies_count }}">
    <div class="container">
        <div class="row">
            <div class="col-md-8">
                <div class="panel panel-default">
                    <div class="panel-heading">
                        <div class="level">
                            <span class="flex">
                                <a href="{{ route('profile', $thread->creator) }}">{{ $thread->creator->name }}</a> posted
                                {{ $thread->title }}
                            </span>

                            @can('delete', $thread)
                                <form METHOD="POST" action="{{ $thread->path() }}">
                                    {{ csrf_field() }}
                                    {{ method_field('DELETE') }}

                                    <button type="submit" class="btn btn-link">Delete</button>
                                </form>
                            @endcan
                        </div>
                    </div>

                    <div class="panel-body">
                        {{ $thread->body }}
                    </div>
                </div>

                <hr>

                <replies @added="repliesCount++"
                         @removed="repliesCount--"></replies>
            </div>

            <div class="col-md-4">
                <div class="panel panel-default">
                    <div class="panel-body">
                        This thread was published {{ $thread->created_at->diffForHumans() }} by
                        <a href="">{{ $thread->creator->name }}</a>, and currently has
                        <span v-text="repliesCount"></span> {{ str_plural('comment', $thread->replies_count) }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</thread-view>
@endsection