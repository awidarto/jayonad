@extends('layout.front')


@section('content')

<h3>{{$title}}</h3>

{{Former::open_for_files($submit,'POST',array('class'=>'custom addAttendeeForm'))}}

{{ Former::hidden('id')->value($formdata['_id']) }}
<div class="row-fluid">
    <div class="span6">

        {{ Former::select('salutation')->options(Config::get('kickstart.salutation'))->label('Salutation')->class('span1') }}
        {{ Former::text('firstname','First Name')->required() }}
        {{ Former::text('lastname','Last Name')->required() }}
        {{ Former::text('mobile','Mobile')->class('span3')->maxlength(15) }}

        {{ Former::text('address_1','Address')->required() }}
        {{ Former::text('address_2','') }}
        {{ Former::text('city','City')->required() }}
        {{ Former::text('zipCode','ZIP / Postal Code')->id('zip')->class('span2')->maxlength(5)->required() }}
        <div class="us" style="display:none;">
            {{ Former::select('state')->class('us')->options(Config::get('country.us_states'))->label('State')->style('display:none;')->id('us_states') }}
        </div>
        <div class="au" style="display:none;">
            {{ Former::select('state')->class('au')->options(Config::get('country.aus_states'))->label('State')->style('display:none;')->id('au_states') }}
        </div>
        <div class="outside">
            {{ Former::text('state','State / Province')->class('outside span3')->id('other_state') }}
        </div>

        {{ Former::select('countryOfOrigin')->id('country')->options(Config::get('country.countries'))->label('Country of Origin') }}
    </div>
    <div class="span6">
        {{ Former::text('email','Email')->required() }}

        {{ Former::password('pass','Password')->help('Leave blank for no changes') }}
        {{ Former::password('repass','Repeat Password') }}

    </div>
</div>

<div class="row-fluid right">
    <div class="span12">
        {{ Form::submit('Save',array('class'=>'btn primary'))}}&nbsp;&nbsp;
        {{ HTML::link($back,'Cancel',array('class'=>'btn'))}}
    </div>
</div>
{{Former::close()}}

{{ HTML::script('js/wysihtml5-0.3.0.min.js') }}
{{ HTML::script('js/parser_rules/advanced.js') }}

<script type="text/javascript">


$(document).ready(function() {

    function setVisibleOptions(){
        var mc = $('#mainCategory').val();

        console.log(mc);

        if( mc == 'Structure'){
            $('#productFunction').hide();
            $('#productSystem').show();
            $('#productApplication').hide();
        }else if( mc == 'Furniture'){
            $('#productFunction').show();
            $('#productSystem').hide();
            $('#productApplication').hide();
        }else{
            $('#productFunction').hide();
            $('#productSystem').hide();
            $('#productApplication').show();
        }

    }

    setVisibleOptions();

    $('#country').on('change',function(){
        var country = $('#country').val();

        if(country == 'Australia'){
            $('.au').show();
            $('.us').hide();
            $('.outside').hide();
            $('select').select2({
              width : 'resolve'
            });
        }else if(country == 'United States of America'){
            $('.au').hide();
            $('.us').show();
            $('.outside').hide();
            $('select').select2({
              width : 'resolve'
            });
        }else{
            $('.au').hide();
            $('.us').hide();
            $('.outside').show();
        }


    });


    $('select').select2({
      width : 'resolve'
    });

    var url = '{{ URL::to('upload') }}';

    $('#fileupload').fileupload({
        url: url,
        dataType: 'json',
        done: function (e, data) {
            $('#progress .bar').css(
                'width',
                '0%'
            );

            $.each(data.result.files, function (index, file) {
                var thumb = '<li><img src="' + file.thumbnail_url + '" /><br /><input type="radio" name="defaultpic" value="' + file.name + '"> Default<br /><span class="img-title">' + file.name + '</span>' +
                '<label for="colour">Colour</label><input type="text" name="colour[]" />' +
                '<label for="material">Material & Finish</label><input type="text" name="material[]" />' +
                '<label for="tags">Tags</label><input type="text" name="tag[]" />' +
                '</li>';
                $(thumb).appendTo('#files ul');

                var upl = '<input type="hidden" name="delete_type[]" value="' + file.delete_type + '">';
                upl += '<input type="hidden" name="delete_url[]" value="' + file.delete_url + '">';
                upl += '<input type="hidden" name="filename[]" value="' + file.name  + '">';
                upl += '<input type="hidden" name="filesize[]" value="' + file.size  + '">';
                upl += '<input type="hidden" name="temp_dir[]" value="' + file.temp_dir  + '">';
                upl += '<input type="hidden" name="thumbnail_url[]" value="' + file.thumbnail_url + '">';
                upl += '<input type="hidden" name="filetype[]" value="' + file.type + '">';
                upl += '<input type="hidden" name="fileurl[]" value="' + file.url + '">';

                $(upl).appendTo('#uploadedform');

            });
        },
        progressall: function (e, data) {
            var progress = parseInt(data.loaded / data.total * 100, 10);
            $('#progress .bar').css(
                'width',
                progress + '%'
            );
        }
    })
    .prop('disabled', !$.support.fileInput)
        .parent().addClass($.support.fileInput ? undefined : 'disabled');

    $('#field_role').change(function(){
        //alert($('#field_role').val());
        // load default permission here
    });

    /*
    var editor = new wysihtml5.Editor('ecofriendly', { // id of textarea element
      toolbar:      'wysihtml5-toolbar', // id of toolbar element
      parserRules:  wysihtml5ParserRules // defined in parser rules set
    });
    */

    $('#name').keyup(function(){
        var title = $('#name').val();
        var slug = string_to_slug(title);
        $('#permalink').val(slug);
    });

    //$('#color_input').colorPicker();

    // dynamic tables
    $('#add_btn').click(function(){
        //alert('click');
        addTableRow($('#variantTable'));
        return false;
    });

    // custom field table
    $('#custom_add_btn').click(function(){
        //alert('click');
        addTableRow($('#customTable'));
        return false;
    });

    $('#related_add_btn').click(function(){
        //alert('click');
        addTableRow($('#relatedTable'));
        return false;
    });

    $('#component_add_btn').click(function(){
        //alert('click');
        addTableRow($('#componentTable'));
        return false;
    });

    $('#mainCategory').change(function(){
        setVisibleOptions();
    });

});

</script>

@stop