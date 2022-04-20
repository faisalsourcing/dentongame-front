<ul>
    <li class="first">
        <div class="left-col">
            <h3>Categories</h3>
        </div>
        <div class="right-col">
            <h3>Current Year</h3>
        </div>
    </li>
        @foreach ($balance as $label=>$detail)
            <li>
                <div class="left-col">
                    <h4 data-toggle="collapse" data-target="#collapsetwo" aria-expanded="false" aria-controls="collapsetwo">{{ $label }}</h4>
                </div>
                <div class="right-col">
                    <h5>{{ $balance[$label]['amount'] }}</h5>
                </div>
            </li>
        @endforeach
</ul>
<div class="clearfix"></div>