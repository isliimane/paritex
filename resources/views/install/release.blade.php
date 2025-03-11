@extends('admin.partials.master')
@section('installed_addon')
    active
@endsection
@section('addon_utility')
    active
@endsection
@section('title')
    {{ __('Available Addons') }}
@endsection
@section('main-content')
    <section class="section">
        <div class="section-body">
            <div class="d-flex justify-content-between">
                <div class="d-block">
                    <h2 class="section-title">{{ __('Installed Addons') }}</h2>
                    <p class="section-lead">
                        {{ __('Create Release') }}
                    </p>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-sm-xs-12 col-md-5">
                <div class="card">
                    <div class="card-header input-title">
                        <h4>{{ __('Install/Update') }}</h4>
                    </div>
                    <div class="card-body card-body-paddding">
                        <form method="POST" action="{{ route('create.release') }}">
                            @csrf
                            <div class="form-group">
                                <label for="prefix">{{ __('Prefix or System Name') }} *</label>
                                <input type="text" name="prefix" id="prefix"
                                       placeholder="i.e yoori"
                                       value="{{ old('prefix') }}" class="form-control" required>
                                @if ($errors->has('prefix'))
                                    <div class="invalid-feedback">
                                        <p>{{ $errors->first('prefix') }}</p>
                                    </div>
                                @endif
                            </div>
                            <div class="form-group">
                                <label for="last_version">{{ __('Latest Version') }} *</label>
                                <input type="text" name="latest_version" id="latest_version"
                                       placeholder="{{ __('Enter latest version')  }}"
                                       value="{{ old('latest_version') }}" class="form-control" required>
                                @if ($errors->has('last_version'))
                                    <div class="invalid-feedback">
                                        <p>{{ $errors->first('latest_version') }}</p>
                                    </div>
                                @endif
                            </div>
                            <div class="form-group">
                                <label for="version">{{ __('Version to be created') }} *</label>
                                <input type="text" name="version" id="version"
                                       placeholder="{{ __('Enter version')  }}"
                                       value="{{ old('version') }}" class="form-control" required>
                                @if ($errors->has('version'))
                                    <div class="invalid-feedback">
                                        <p>{{ $errors->first('version') }}</p>
                                    </div>
                                @endif
                            </div>
                            <div class="form-group">
                                <label for="latest_commit">{{ __('Latest Commit') }} *</label>
                                <input type="text" name="latest_commit" id="latest_commit"
                                       placeholder="{{ __('Enter latest commit')  }}"
                                       value="{{ old('latest_commit') }}" class="form-control" required>
                                @if ($errors->has('latest_commit'))
                                    <div class="invalid-feedback">
                                        <p>{{ $errors->first('latest_commit') }}</p>
                                    </div>
                                @endif
                            </div>
                            <div class="form-group">
                                <label for="old_commit">{{ __('Old Commit') }} *</label>
                                <input type="text" name="old_commit" id="old_commit"
                                       placeholder="{{ __('Enter latest commit')  }}"
                                       value="{{ old('old_commit') }}" class="form-control" required>
                                @if ($errors->has('old_commit'))
                                    <div class="invalid-feedback">
                                        <p>{{ $errors->first('old_commit') }}</p>
                                    </div>
                                @endif
                            </div>

                            <div class="form-group text-right">
                                <button type="submit" class="btn btn-outline-primary" tabindex="4">
                                    {{ __('Save') }}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection

