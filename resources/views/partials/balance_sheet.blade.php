<ul style="width: 90%;">
    @foreach ($cashBalance as $label=>$detail)
        <li>
            <div class="left-col">
                <h4>{{ $label }}</h4>
            </div>
            <div class="right-col">
                <h5>{{ $cashBalance[$label]['amount'] }}</h5>
            </div>
        </li>
    @endforeach
</ul>
<div class="clearfix"></div>