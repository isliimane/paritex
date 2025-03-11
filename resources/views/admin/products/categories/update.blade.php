@extends('admin.partials.master')
@section('category_active')
    active
@endsection
@section('product_active')
    active
@endsection
@section('title')
    {{ __('Update') }}
@endsection
@section('main-content')
    <section class="section">
        <div class="section-body">
            <div class="d-flex justify-content-between">
                <div class="d-block">
                    <h2 class="section-title">{{__('Update Category')}}</h2>
                </div>
                @if(hasPermission('category_read'))
                    <div class="buttons add-button">
                        <a href="{{ old('r') ? old('r') : (@$r ? $r : url()->previous() )}}"
                           class="btn btn-outline-primary"><i class='bx bx-arrow-back'></i>{{ __('Back') }}</a>
                    </div>
                @endif
            </div>
            <div class="row">
                <div class="col-6 middle">
                    <div class="card">
                        <div class="card-header input-title">
                            <h4>{{__('Update Category')}}</h4>
                        </div>
                        <div class="card-body card-body-paddding">
                            <form class="" id="lang">
                                <div class="form-group">
                                    <label for="name">{{ __('Language') }}</label>
                                    <input type="hidden"
                                           value="{{ old('r') ? old('r') : (@$r ? $r : url()->previous() )}}" name="r">
                                    <select class="form-control selectric lang" name="lang">
                                        <option value="">{{ __('Select Language') }}</option>
                                        @foreach($languages as $language)
                                            <option value="{{ $language->locale }}" {{($lang != '' ? ($language->locale == $lang ? 'selected' : '') : ($language->locale == 'en' ? 'selected' : '')) }}>{{ $language->name }}</option>
                                        @endforeach
                                    </select>

                                    @if ($errors->has('lang'))
                                        <div class="invalid-feedback">
                                            <p>{{ $errors->first('lang') }}</p>
                                        </div>
                                    @endif
                                </div>
                            </form>
                            <form method="POST" action="{{route('update.categories')}}" enctype="multipart/form-data">
                                @csrf
                                @method('put')

                                <div class="form-group">
                                    <label for="title">{{ __('Title') }} *</label>
                                    <input type="hidden"
                                           value="{{ $category_language->translation_null == 'not-found' ? '' : $category_language->id }}"
                                           name="cat_lang_id">
                                    <input type="hidden" value="{{ $category_language->category->id }}"
                                           name="category_id">
                                    <input type="hidden" value="{{ $lang }}" name="lang">
                                    <input type="hidden"
                                           value="{{ old('r') ? old('r') : (@$r ? $r : url()->previous() )}}" name="r">
                                    <input type="text" name="title" id="title"
                                           value="{{ old('title') ? old('title') : $category_language->title }}"
                                           class="form-control">
                                    @if ($errors->has('title'))
                                        <div class="invalid-feedback">
                                            <p>{{ $errors->first('title') }}</p>
                                        </div>
                                    @endif
                                </div>
                                <div class="form-group">
                                    <label for="name">{{ __('Root Category') }}</label>
                                    <select class="form-control filter-categories-by-ajax" name="category">
                                        @if($parent_category)
                                            <option value="{{ $parent_category->id }}">{{ $parent_category->getTranslation('title', App::getLocale()) }}</option>
                                        @endif
                                    </select>
                                    @if ($errors->has('category'))
                                        <div class="invalid-feedback">
                                            <p>{{ $errors->first('category') }}</p>
                                        </div>
                                    @endif
                                </div>
                                <div class="form-group">
                                    <label for="ordering">{{__('Order')}}
                                        (<small>{{ __('To show on menu sidebar') }}</small>)</label>
                                    <input id="ordering" type="number" class="form-control" name="ordering"
                                           value="{{ old('ordering') ? old('ordering') : $category_language->category->ordering }}"
                                           placeholder="{{__('Slug')}}" tabindex="1">
                                    @if ($errors->has('ordering'))
                                        <div class="invalid-feedback">
                                            <p>{{ $errors->first('ordering') }}</p>
                                        </div>
                                    @endif
                                </div>
                                <div class="form-group">
                                    <label for="slug">{{__('Slug')}}</label>
                                    <input id="slug" type="text" class="form-control" name="slug"
                                           value="{{ old('slug') ? old('slug') : $category_language->category->slug }}"
                                           placeholder="{{__('Order')}}" tabindex="1">
                                    @if ($errors->has('slug'))
                                        <div class="invalid-feedback">
                                            <p>{{ $errors->first('slug') }}</p>
                                        </div>
                                    @endif
                                </div>
                                <div class="form-group">
                                    <label for="commission">{{__('Commission Rate')}}</label>
                                    <input id="commission" type="number" class="form-control" step="any"
                                           name="commission"
                                           value="{{ old('commission') ? old('commission') : $category_language->category->commission }}"
                                           placeholder="{{__('Commission Rate')}} {{__('(%)')}}" tabindex="1">

                                    @if ($errors->has('commission'))
                                        <div class="invalid-feedback">
                                            <p>{{ $errors->first('commission') }}</p>
                                        </div>
                                    @endif
                                </div>
                                @if(addon_is_activated('ramdhani'))
                                    <div class="row">
                                        <div class="col-md-6">
                                            <h6>{{ __('Price Calculator') }}</h6>
                                        </div>
                                        <div class="col-md-6 text-right">
                                            <a href="javascript:void(0)" class="btn btn-outline-primary ml-2"
                                               id="add-item">{{ __('Add More') }}</a>
                                        </div>
                                    </div>
                                    <table class="table table-bordered price_calculator_table">
                                        <thead>
                                        <tr>
                                            <th>{{ __('Minimum Price') }}</th>
                                            <th>{{ __('Maximum Price') }}</th>
                                            <th>{{ __('Price Multiplier') }}</th>
                                            <th>{{ __('Action') }}</th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        @if(count($ranges) > 0)
                                            @foreach($ranges as $key=> $range)
                                                <tr>
                                                    <td>
                                                        <input type="number"
                                                               class="form-control mb-2 mr-sm-2 min_price"
                                                               data-id="{{ $key }}"
                                                               name="ranges[{{ $key }}][min_price]"
                                                               value="{{ priceFormatUpdate($range['min_price'],settingHelper('default_currency'),'*') }}" readonly
                                                               min="0" required
                                                               placeholder="{{__('Minimum Price')}}">
                                                    </td>
                                                    <td>
                                                        <input type="number" step="any"
                                                               class="form-control mb-2 mr-sm-2 max_price"
                                                               data-id="{{ $key }}"
                                                               name="ranges[{{ $key }}][max_price]"
                                                               value="{{ priceFormatUpdate($range['max_price'],settingHelper('default_currency'),'*') }}" min="0" required
                                                               placeholder="{{__('Maximum Price')}}">
                                                    </td>
                                                    <td>
                                                        <input type="number"
                                                               class="form-control mb-2 mr-sm-2"
                                                               id="link" name="ranges[{{ $key }}][multiplier]"
                                                               value="{{ $range->multiplier }}" required
                                                               min="0" step="any"
                                                               placeholder="{{__('Price Multiplier')}}">
                                                    </td>
                                                    @if($key > 0)
                                                        <td>
                                                            <button type="button"
                                                                    class="btn btn-icon btn-sm btn-danger remove-row"><i
                                                                        class="bx bx-trash"></i></button>
                                                        </td>
                                                    @endif

                                                </tr>
                                            @endforeach
                                        @else
                                            @include('admin.products.categories.ranges')
                                        @endif
                                        </tbody>
                                    </table>
                                @endif
                                <div class="form-group">
                                    <label for="barcode">{{ __('Icon') }}</label>
                                    <div class="input-group category-icon-selector">
                                        <div class="input-group-append" id="uip-select-input">
                                            <div class="input-group-text"
                                                 id="uip-icon-container">
                                                <i class="{{ $category_language->category->icon }}">{{ $category_language->category->icon ? '' : '?' }}</i>
                                            </div>
                                        </div>
                                        <input type="text" name="icon" id="uip-select-input-value"
                                               value="{{ old('icon') ? old('icon') : $category_language->category->icon }}"
                                               class="form-control use-material-icon-picker"
                                               placeholder="{{ __('Enter product icon') }}">
                                    </div>

                                    @if ($errors->has('icon'))
                                        <div class="invalid-feedback">
                                            <p>{{ $errors->first('icon') }}</p>
                                        </div>
                                    @endif
                                </div>
                                <div class="form-group">
                                    <label for="logo">{{ __('Thumbnail') }} ({{ __('72*72') }})</label>
                                    <div class="form-group">
                                        <div class="input-group gallery-modal" id="btnSubmit" data-for="image"
                                             data-selection="single"
                                             data-target="#galleryModal" data-dismiss="modal">
                                            <input type="hidden" name="logo"
                                                   value="{{ old('logo') !='' ? old('logo') : $category_language->category->logo_id }}"
                                                   class="image-selected">
                                            <span class="form-control"><span
                                                        class="counter">{{ $category_language->category->logo_id ? 1 : 0 }}</span> {{ __('file chosen') }}</span>
                                            <div class="input-group-prepend">
                                                <div class="input-group-text">
                                                    {{ __('Choose File') }}
                                                </div>
                                            </div>
                                        </div>

                                        <div class="selected-media-box">
                                            <div class="mt-4 gallery gallery-md d-flex">
                                                @php
                                                    $thumb = old('logo') ? old('logo') : $category_language->category->logo_id;
                                                    $thumbnail = \App\Models\Media::find($thumb);
                                                @endphp
                                                @if($thumbnail)
                                                    <div class="selected-media mr-2 mb-2 mt-3 ml-0"
                                                         data-id="{{ $thumbnail->id }}">
                                                        @if(is_file_exists($thumbnail->image_variants['image_72x72'], $thumbnail->image_variants['storage']))
                                                            <img
                                                                    src="{{ get_media($thumbnail->image_variants['image_72x72'], $thumbnail->image_variants['storage'])}}"
                                                                    alt="img-thumbnail"
                                                                    class="img-thumbnail logo-profile">
                                                        @else
                                                            <img
                                                                    src="{{ static_asset('images/default/default-image-72x72.png') }}"
                                                                    alt="img-thumbnail"
                                                                    class="img-thumbnail logo-profile">
                                                        @endif
                                                        <div class="image-remove">
                                                            <a href="javascript:void(0)" class="remove"><i
                                                                        class="bx bx-x"></i></a>
                                                        </div>
                                                    </div>
                                                @else
                                                    <div class="selected-media mr-2 mb-2 mt-3 ml-0">
                                                        <img src="{{ static_asset('images/default/default-image-72x72.png') }}"
                                                             alt="{{ $category_language->title }}"
                                                             class="img-thumbnail logo-profile">
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label for="logo">{{ __('Banner') }} ({{ __('835*200') }})</label>
                                    <div class="form-group">
                                        <div class="input-group gallery-modal" id="btnSubmit" data-for="image"
                                             data-selection="single"
                                             data-target="#galleryModal" data-dismiss="modal">
                                            <input type="hidden" name="banner"
                                                   value="{{ old('banner') !='' ? old('banner') : $category_language->category->banner_id }}"
                                                   class="image-selected">
                                            <span class="form-control"><span
                                                        class="counter">{{ $category_language->category->banner_id ? 1 : 0 }}</span> {{ __('file chosen') }}</span>
                                            <div class="input-group-prepend">
                                                <div class="input-group-text">
                                                    {{ __('Choose File') }}
                                                </div>
                                            </div>
                                        </div>

                                        <div class="selected-media-box">
                                            <div class="mt-4 gallery gallery-md d-flex">
                                                @php
                                                    $banner = old('banner') ? old('banner') : $category_language->category->banner_id;
                                                    $thumbnail = \App\Models\Media::find($banner);
                                                @endphp
                                                @if($thumbnail)
                                                    <div class="selected-media mr-2 mb-2 mt-3 ml-0"
                                                         data-id="{{ $thumbnail->id }}">
                                                        @if(is_file_exists($thumbnail->image_variants['image_72x72'], $thumbnail->image_variants['storage']))
                                                            <img
                                                                    src="{{ get_media($thumbnail->image_variants['image_72x72'], $thumbnail->image_variants['storage'])}}"
                                                                    alt="img-thumbnail"
                                                                    class="img-thumbnail logo-profile">
                                                        @else
                                                            <img
                                                                    src="{{ static_asset('images/default/default-image-72x72.png') }}"
                                                                    alt="img-thumbnail"
                                                                    class="img-thumbnail logo-profile">
                                                        @endif
                                                        <div class="image-remove">
                                                            <a href="javascript:void(0)" class="remove"><i
                                                                        class="bx bx-x"></i></a>
                                                        </div>
                                                    </div>
                                                @else
                                                    <div class="selected-media mr-2 mb-2 mt-3 ml-0">
                                                        <img src="{{ static_asset('images/default/default-image-72x72.png') }}"
                                                             alt="{{ $category_language->title }}"
                                                             class="img-thumbnail logo-profile">
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label for="meta_title">{{__('Meta Title')}}</label>
                                    <input id="meta_title" type="text" class="form-control" name="meta_title"
                                           placeholder="{{__('Meta title')}}" tabindex="1"
                                           value="{{ old('meta_title') ? old('meta_title') : $category_language->meta_title }}">
                                    @if ($errors->has('meta_title'))
                                        <div class="invalid-feedback">
                                            <p>{{ $errors->first('meta_title') }}</p>
                                        </div>
                                    @endif
                                </div>
                                <div class="form-group">
                                    <label for="meta_description">{{__('Meta description')}}</label>
                                    <textarea class="form-control" name="meta_description" id="meta_description"
                                              placeholder="{{__('Description')}}">{{$category_language->meta_description}}</textarea>
                                    @if ($errors->has('meta_description'))
                                        <div class="invalid-feedback">
                                            <p>{{ $errors->first('meta_description') }}</p>
                                        </div>
                                    @endif
                                </div>
                                <div class="form-group text-right">
                                    <button type="submit" class="btn btn-outline-primary" tabindex="4">
                                        {{ __('Update') }}
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    @if(addon_is_activated('ramdhani'))
        <div class="modal">
            <table>
                <tbody>
                @include('admin.products.categories.ranges')
                </tbody>
            </table>
        </div>
    @endif
    @include('admin.common.selector-modal')
@endsection

@section('style')
    <link rel="stylesheet" href="{{ static_asset('admin/css/dropzone.css') }}">
@endsection
@push('script')
    <script type="text/javascript" src="{{ static_asset('admin/js/dropzone.min.js') }}"></script>
    <script type="text/javascript" src="{{ static_asset('admin/js/ajax-live-search.js') }}"></script>
    <script>
        (function () {
            var material_icons = [];

            fetch('{{ static_asset('admin/js/meta.json') }}').then((response) => {
                return response.json();
            }).then(data => {
                material_icons = data;
                $('input[type="text"].use-material-icon-picker').each(function () {
                    // Append the picker and the search box.
                    var $picker = $('<div class="material-icon-picker" tabindex="-1"></div>');
                    var $search = $('<input type="text" class="form-control mb-3" placeholder="Search...">');
                    // Do simple filtering based on the search.
                    $search.on('keyup', function () {
                        var search = $search.val().toLowerCase();
                        let icons = material_icons.filter(
                            (d) =>
                                (d.name && d.name.toLowerCase().includes(search))
                        );
                        iconAppend(icons, 201);

                        var $icons = $(this).closest('.material-icon-picker');
                        $icons.find('.mdi-custom-icon').css('display', 'none');
                        $icons.find('.mdi-custom-icon:contains(' + search + ')').css('display', 'inline-block');
                    });
                    $picker.append($search);
                    // Append each icon into the picker.
                    var $icons = $('<div class="icons pb-3"></div>');
                    iconAppend(material_icons, 201);

                    // Show the picker when the input field gets focus.
                    $picker.append($icons).hide();
                    $(this).after($picker);

                    function onIconClick() {
                        let val = "mdi mdi-" + $(this).text();
                        $(this).closest('.material-icon-picker').prev().val(val).trigger('change');
                        $('#uip-icon-container i').attr('class', '').addClass(val).text('');
                        $picker.fadeOut(201);
                    }

                    function iconAppend(icons, limit) {
                        icons.forEach(function (icon, index) {
                            if (index < limit) {
                                let class_name = "mdi mdi-" + icon.name;
                                var $icon = $('<div class="mdi-custom-icon" title="' + icon.name + '"><i class="' + class_name + '"></i><p>' + icon.name + '</p></div>');
                                $('.class_name').text(icon.name);
                                $icon.on('click', onIconClick);
                                $icons.append($icon);
                            }
                        });

                    }
                });
            });
            // Hide any picker when it or the input field loses focus.
            $(document).on('mouseup', function (e) {
                var $picker = $('.material-icon-picker');
                if ($picker.length && !$picker.is(e.target) && !$(e.target).hasClass('use-material-icon-picker') && $picker.has(e.target).length === 0) {
                    $picker.fadeOut(201);
                }
            });
            $(document).on('click', '#uip-icon-container', function (e) {
                var $picker = $('.material-icon-picker');
                $picker.fadeIn(201);
            });
            $(document).on('keyup', '#uip-select-input-value', function (e) {
                var val = $(this).val();
                $('#uip-icon-container i').addClass(val).text('');
            });
            @if(addon_is_activated('ramdhani'))
            $(document).on('click', '#add-item', function () {
                let selector = $('.price_calculator_table tbody tr:last-child .max_price');
                var html = $('.modal table tbody').html();
                $('.price_calculator_table tbody').append(html);
                let last_row = $('.price_calculator_table tbody tr:last-child');
                $.each(last_row.find('input'), function (index, value) {
                    let name = $(value).attr('name');
                    let length = parseInt($('.price_calculator_table tbody tr').length) - 1;
                    let new_name = name.replace('[0]', '[' + length + ']');
                    $(value).attr('name', new_name);
                });
                minPriceCalculator(selector);
            });
            $(document).on('click', '.remove-row', function () {
                $(this).closest('tr').remove();
            });
            $(document).on('focusout', '.max_price', function () {
                let val = $(this).val();
                let min_val = parseFloat($(this).closest('tr').find('.min_price').val());

                if (val < min_val) {
                    toastr.warning('Maximum price must be greater than minimum price');
                    $(this).val(min_val);
                }
                minPriceCalculator(this);
            });
            @endif
        }());

        function minPriceCalculator(selector) {
            let length = $('.max_price').length;
            if (length > 1) {
                let max_price = parseFloat($(selector).val());
                $(selector).closest('tr').next().find('.min_price').val(max_price + 1);
            }
        }
    </script>
@endpush
