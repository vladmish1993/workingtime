<script
        src="https://code.jquery.com/jquery-3.6.1.min.js"
        integrity="sha256-o88AwQnZB+VDvE9tvIXrMQaPlFFSUTR+nldQm1LuPXQ="
        crossorigin="anonymous"></script>

<style>
    #schedule div {
        width: 150px;
        padding: 4px 0px 4px 5px;
        border-bottom: 1px solid grey;
        color: black;
    }

    .inactive {
        background: grey;
        opacity: 0.8;
    }

    .active {
        background: #00ad1d;
        opacity: 0.8;
        color: white !important;
    }

    #nextOpen
    {
        padding-top: 10px;
    }
</style>

<form action="actions.php" id="form">
    <label for="timezone">Select timezone:</label>
    <br>
    <select id="timezone" name="timezone">
        <option id="<?= date_default_timezone_get() ?>" selected="selected"><?= date_default_timezone_get() ?></option>
    </select>
</form>

<div>
    <div style="padding-bottom: 10px;font-weight: bold;">Schedule:</div>
    <div id="schedule"></div>
    <div id="nextOpen"></div>
</div>

<script>
    $(document).ready(function () {
        //get test timezones
        $.ajax({
            url: '/actions.php',
            method: 'get',
            dataType: 'json',
            async: false,
            data: {'action': 'get_timezones'},
            success: function (data) {
                var def_timezone = $("#timezone option").val();

                $.each(data, function (key, value) {
                    if (value != def_timezone) {
                        $('#timezone').append('<option value="' + value + '">' + value + '</option>')
                    }
                });
            }
        });

        //first fill with user default timezone
        var timezone = $("#timezone").val();
        var isOpen;
        var className;

        $('#schedule').empty();
        $('#nextOpen').empty();

        $.ajax({
            url: '/actions.php',
            method: 'get',
            dataType: 'json',
            async: false,
            data: {'action': 'get_work_time', 'userTimezone': timezone},
            success: function (data) {

                $.each(data, function (key, value) {
                    className = value.hasOwnProperty('active') ? 'active' : 'inactive';
                    if(className == 'active') isOpen = true;
                    $('#schedule').append('<div class="' + className + '">' + key + ' : ' + value['workTime']['from'] + ' - ' + value['workTime']['to'] + '</div>')
                });
            }
        });

        if(!isOpen)
        {
            $.ajax({
                url: '/actions.php',
                method: 'get',
                dataType: 'json',
                async: false,
                data: {'action': 'get_open', 'userTimezone': timezone},
                success: function (data) {
                    $('#nextOpen').html('The shop will be open  <b>' + data['text']+'</b>');
                }
            });
        }

        //Change timezone select
        $("#timezone").change(function (event) {
            var timezone = $("#timezone").val();
            isOpen = false;

            $('#schedule').empty();
            $('#nextOpen').empty();

            $.ajax({
                url: '/actions.php',
                method: 'get',
                dataType: 'json',
                async: false,
                data: {'action': 'get_work_time', 'userTimezone': timezone},
                success: function (data) {
                    $.each(data, function (key, value) {
                        className = value.hasOwnProperty('active') ? 'active' : 'inactive';
                        if(className == 'active') isOpen = true;
                        $('#schedule').append('<div class="' + className + '">' + key + ' : ' + value['workTime']['from'] + ' - ' + value['workTime']['to'] + '</div>')
                    });
                }
            });

            if(!isOpen)
            {
                $.ajax({
                    url: '/actions.php',
                    method: 'get',
                    dataType: 'json',
                    data: {'action': 'get_open','userTimezone': timezone},
                    success: function (data) {
                        $('#nextOpen').html('The shop will be open  <b>' + data['text']+'</b>');
                    }
                });
            }
        });
    });
</script>