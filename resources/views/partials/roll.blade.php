<div class="popup-orange">
    <div class="inner">
        <div class="main-hd orange-tab">{{ $data->name }}</div>
        <div class="detail">
            <div class="col-12 text-center">
                <div class="dice">
                    <ol class="die-list odd-roll" data-roll="1" id="die-2">
                        <li class="die-item" data-side="1">
                            <span class="dot"></span>
                        </li>
                        <li class="die-item" data-side="2">
                            <span class="dot"></span>
                            <span class="dot"></span>
                        </li>
                        <li class="die-item" data-side="3">
                            <span class="dot"></span>
                            <span class="dot"></span>
                            <span class="dot"></span>
                        </li>
                        <li class="die-item" data-side="4">
                            <span class="dot"></span>
                            <span class="dot"></span>
                            <span class="dot"></span>
                            <span class="dot"></span>
                        </li>
                        <li class="die-item" data-side="5">
                            <span class="dot"></span>
                            <span class="dot"></span>
                            <span class="dot"></span>
                            <span class="dot"></span>
                            <span class="dot"></span>
                        </li>
                        <li class="die-item" data-side="6">
                            <span class="dot"></span>
                            <span class="dot"></span>
                            <span class="dot"></span>
                            <span class="dot"></span>
                            <span class="dot"></span>
                            <span class="dot"></span>
                        </li>
                    </ol>
                </div>
            </div>
            <div class="col-12 text-center mb-3" id="roll_msg">

            </div>
            <br />
            <input type="button" id="roll_now" class="btn" value="Roll" />
            <input type="button" class="btn" value="Okay" id="close_popup" style="display: none;" />
        </div>
    </div>
</div>