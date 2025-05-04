$(function() {

    "use strict";

    $(document).ready(function () {
        $('#country-dropdown').on('change', function () {
            var country_id = this.value;
            $("#state-dropdown").html('');
            $.ajax({
                url: $('#url').val() + '/admin/get-states-by-country',
                type: "POST",
                data: {
                    country_id: country_id,
                    _token: $('meta[name="csrf-token"]').attr('content')
                },
                dataType: 'json',
                success: function (result) {
                    $.each(result.states, function (key, value) {
                        $("#state-dropdown").append('<option value="' + value.id + '">' + value.name + '</option>');
                    });
                    $('#city-dropdown').html('<option value="">Select State First</option>');
                }
            });


        });
        $('#shipping-country-dropdown').on('change', function () {
            var country_id = this.value;
            $("#shipping-state-dropdown").html('');
            $.ajax({
                url: $('#url').val() + '/admin/get-states-by-country',
                type: "POST",
                data: {
                    country_id: country_id,
                    _token: $('meta[name="csrf-token"]').attr('content')
                },
                dataType: 'json',
                success: function (result) {
                    $.each(result.states, function (key, value) {
                        $("#shipping-state-dropdown").append('<option value="' + value.id + '">' + value.name + '</option>');
                    });
                    $('#shipping-city-dropdown').html('<option value="">Select State First</option>');
                }
            });


        });
        $('#billing-country-dropdown').on('change', function () {
            var country_id = this.value;
            $("#billing-state-dropdown").html('');
            $.ajax({
                url: $('#url').val() + '/admin/get-states-by-country',
                type: "POST",
                data: {
                    country_id: country_id,
                    _token: $('meta[name="csrf-token"]').attr('content')
                },
                dataType: 'json',
                success: function (result) {
                    $.each(result.states, function (key, value) {
                        $("#billing-state-dropdown").append('<option value="' + value.id + '">' + value.name + '</option>');
                    });
                    $('#billing-city-dropdown').html('<option value="">Select State First</option>');
                }
            });


        });
        $('#state-dropdown').on('change', function () {
            var state_id = this.value;
            $("#city-dropdown").html('');
            $.ajax({
                url: $('#url').val() + '/admin/get-cities-by-state',
                type: "POST",
                data: {
                    state_id: state_id,
                    _token: $('meta[name="csrf-token"]').attr('content')
                },
                dataType: 'json',
                success: function (result) {
                    $.each(result.cities, function (key, value) {
                        $("#city-dropdown").append('<option value="' + value.id + '">' + value.name + '</option>');
                    });
                }
            });


        });
        $('#shipping-state-dropdown').on('change', function () {
            var state_id = this.value;
            $("#shipping-city-dropdown").html('');
            $.ajax({
                url: $('#url').val() + '/admin/get-cities-by-state',
                type: "POST",
                data: {
                    state_id: state_id,
                    _token: $('meta[name="csrf-token"]').attr('content')
                },
                dataType: 'json',
                success: function (result) {
                    $.each(result.cities, function (key, value) {
                        $("#shipping-city-dropdown").append('<option value="' + value.id + '">' + value.name + '</option>');
                    });
                }
            });


        });
        $('#billing-state-dropdown').on('change', function () {
            var state_id = this.value;
            $("#billing-city-dropdown").html('');
            $.ajax({
                url: $('#url').val() + '/admin/get-cities-by-state',
                type: "POST",
                data: {
                    state_id: state_id,
                    _token: $('meta[name="csrf-token"]').attr('content')
                },
                dataType: 'json',
                success: function (result) {
                    $.each(result.cities, function (key, value) {
                        $("#billing-city-dropdown").append('<option value="' + value.id + '">' + value.name + '</option>');
                    });
                }
            });


        });
    });

});
