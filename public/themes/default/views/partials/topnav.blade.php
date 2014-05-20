<?php
    function sa($item){
        if(URL::to($item) == URL::full() ){
            return  'class="active"';
        }else{
            return '';
        }
    }
?>
<ul class="nav">
    @if(Auth::check())

        <li><a href="{{ URL::to('dashboard') }}" {{ sa('dashboard') }} >Dashboard</a></li>

        <li><a href="{{ URL::to('adinventory') }}" {{ sa('adinventory') }} >Ads</a></li>
        <li><a href="{{ URL::to('productcategory') }}" {{ sa('productcategory') }} >Category</a></li>
        <li><a href="{{ URL::to('outlet') }}" {{ sa('outlet') }} >Outlets</a></li>

        <li><a href="{{ URL::to('transaction') }}" {{ sa('transaction') }} >Traffic</a></li>

        <li><a href="{{ URL::to('advertiser') }}" {{ sa('advertiser') }} >Advertisers</a></li>

        <li><a href="{{ URL::to('audience') }}" {{ sa('audience') }} >Audience</a></li>
        <li class="dropdown">
            <a class="dropdown-toggle" data-toggle="dropdown" href="#">
                Reports
                <b class="caret"></b>
              </a>
            <ul class="dropdown-menu">
                <li><a href="{{ URL::to('report/siteaccess') }}" {{ sa('report/siteaccess') }} >Site Access</a></li>
                <li><a href="{{ URL::to('report/activity') }}" {{ sa('report/activity') }} >Activity</a></li>
            </ul>
        </li>

        <li class="dropdown">
            <a class="dropdown-toggle" data-toggle="dropdown" href="#">
                System
                <b class="caret"></b>
              </a>
            <ul class="dropdown-menu">
                <li><a href="{{ URL::to('user') }}" {{ sa('user') }} >Admins</a></li>
                <li><a href="{{ URL::to('option') }}" {{ sa('option') }} >Options</a></li>
            </ul>
        </li>

        {{--

        <li class="dropdown">
            <a class="dropdown-toggle" data-toggle="dropdown" href="#">
                Site Content
                <b class="caret"></b>
              </a>
            <ul class="dropdown-menu">
                <li><a href="{{ URL::to('content/pages') }}" {{ sa('content/pages') }} >Pages</a></li>
                <li><a href="{{ URL::to('content/posts') }}" {{ sa('content/posts') }} >Posts</a></li>
                <li><a href="{{ URL::to('section') }}" {{ sa('section') }} >Section</a></li>
                <li><a href="{{ URL::to('content/category') }}" {{ sa('content/category') }} >Category</a></li>
                <li><a href="{{ URL::to('content/menu') }}" {{ sa('content/menu') }} >Menu</a></li>
            </ul>
        </li>
        --}}
    @endif
</ul>
