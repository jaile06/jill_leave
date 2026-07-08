/**
 * jill_leave 請假表單前端邏輯
 *
 * 依起訖日期動態生成每日代課卡片（跳過週日）：
 * - 每張卡片可設定支付方式（自費/公費）與代課類型（日薪/鐘點）
 * - 日薪：整天一位代課老師（教學組派代或自行覓代）
 * - 鐘點：勾選節次，逐節填寫科目與代課老師
 *
 * 送出時將卡片內容序列化為後端 Jill_leave::save_substitutes() 需要的平行陣列：
 * substitute_date[] / class_period[] / subject[] / substitute_teacher[] / pay[] / type[]
 *
 * 卡片 HTML 結構定義於 op_jill_leave_create.tpl 的 <template>，
 * 語系文字與既有資料由同樣板的 LEAVE_FORM 設定物件傳入。
 */
(function ($) {
    'use strict';

    var cfg = {};
    var $container;
    var card_seq = 0; // radio 群組名稱流水號，確保每張卡片的群組獨立

    // 將 Date 物件格式化為 yyyy-mm-dd
    function fmt_date(d) {
        var m = ('0' + (d.getMonth() + 1)).slice(-2);
        var day = ('0' + d.getDate()).slice(-2);
        return d.getFullYear() + '-' + m + '-' + day;
    }

    // 取得起訖日期間的工作日（跳過週日），回傳 ['yyyy-mm-dd', ...]
    function find_work_dates(start, end) {
        var dates = [];
        var d = new Date(start);
        var end_date = new Date(end);
        for (; d <= end_date; d.setDate(d.getDate() + 1)) {
            if (d.getDay() !== 0) {
                dates.push(fmt_date(d));
            }
        }
        return dates;
    }

    // 日期加上星期文字，如 2026-07-07（二）
    function date_label(date_str) {
        return date_str + '（' + cfg.weekdays[new Date(date_str).getDay()] + '）';
    }

    // 產生一張代課日期卡片（依偏好設定中的節次選項生成節次列）
    function build_card(date_str) {
        var seq = card_seq++;
        var $card = $($.trim($('#substitute_card_tpl').html()));
        $card.attr('data-date', date_str);
        $card.find('.substitute-date-text').text(date_label(date_str));
        $card.find('.pay-radio').attr('name', 'ui_pay_' + seq);
        $card.find('.type-radio').attr('name', 'ui_type_' + seq);
        $card.find('.daily-panel .teacher-opt').attr('name', 'ui_teacher_d' + seq);

        var $hour_panel = $card.find('.hour-panel');
        $.each(cfg.periods || [], function (i, p) {
            var $row = $($.trim($('#period_row_tpl').html()));
            $row.attr('data-period', p);
            $row.find('.period-text').text(p);
            $row.find('.teacher-opt').attr('name', 'ui_teacher_' + seq + '_' + i);
            $hour_panel.append($row);
        });
        return $card;
    }

    // 更新「同第一天」按鈕顯示狀態：第一張隱藏，其餘顯示
    function update_copy_buttons() {
        $container.children('.substitute-card').each(function (i) {
            $(this).find('.copy-first-day-btn').toggleClass('d-none', i === 0);
        });
    }

    // 將第一張卡片的科目與代課老師複製到目標卡片（班級欄位不動，讓使用者自行修改）
    function copy_from_first_card($target_card) {
        var $first_card = $container.children('.substitute-card').first();
        if (!$first_card.length || $first_card[0] === $target_card[0]) {
            return;
        }

        // 複製支付方式與代課類型
        var first_pay = $first_card.find('.pay-radio:checked').val();
        var first_type = $first_card.find('.type-radio:checked').val();
        $target_card.find('.pay-radio[value="' + first_pay + '"]').prop('checked', true);
        $target_card.find('.type-radio[value="' + first_type + '"]').prop('checked', true).trigger('change');

        if (first_type === 'daily') {
            // 日薪：複製代課老師
            var $first_daily = $first_card.find('.daily-panel');
            var $target_daily = $target_card.find('.daily-panel');
            var teacher_mode = $first_daily.find('.teacher-opt:checked').val();
            $target_daily.find('.teacher-opt[value="' + teacher_mode + '"]').prop('checked', true);
            $target_daily.find('.teacher-input').val($first_daily.find('.teacher-input').val());
            sync_row($target_daily, true);
        } else {
            // 鐘點：先從第一天取得預設代課老師與科目（取第一個已勾選節次）
            var $first_checked = $first_card.find('.period-row .period-check:checked').first().closest('.period-row');
            var default_teacher_mode = 'assign';
            var default_teacher_name = '';
            var default_subject = '';
            if ($first_checked.length) {
                default_teacher_mode = $first_checked.find('.teacher-opt:checked').val() || 'assign';
                default_teacher_name = $first_checked.find('.teacher-input').val() || '';
                default_subject = $.trim($first_checked.find('.subject-input').val());
            }

            // 同節次只增加勾選，不取消目標已勾的節次
            $first_card.find('.period-row').each(function () {
                var $src = $(this);
                var period = $src.attr('data-period');
                var $dst = $target_card.find('.period-row[data-period="' + period + '"]');
                if (!$dst.length) { return; }

                if ($src.find('.period-check').is(':checked')) {
                    $dst.find('.period-check').prop('checked', true);
                }
                sync_row($dst, $dst.find('.period-check').is(':checked'));
            });

            // 所有已勾選的節次統一套用第一天的科目與代課老師
            $target_card.find('.period-row').each(function () {
                var $row = $(this);
                if (!$row.find('.period-check').is(':checked')) { return; }
                if (default_subject !== '') {
                    $row.find('.subject-input').val(default_subject);
                }
                $row.find('.teacher-opt[value="' + default_teacher_mode + '"]').prop('checked', true);
                $row.find('.teacher-input').val(default_teacher_name);
                sync_row($row, true);
            });
        }
    }

    // 目前是否為級任（科任才需逐節填班級）
    function is_advisor() {
        return $('input[name="is_advisor"]:checked').val() === '1';
    }

    // 依勾選狀態啟用／停用某範圍（節次列或日薪區）的欄位
    function sync_row($scope, enabled) {
        $scope.find('.subject-input, .teacher-opt').prop('disabled', !enabled);
        // 逐節班級欄位僅科任且該節勾選時開放
        $scope.find('.gc-grade, .gc-class').prop('disabled', !enabled || is_advisor());
        var use_input = $scope.find('.teacher-opt[value="input"]').is(':checked');
        $scope.find('.teacher-input').prop('disabled', !enabled || !use_input);
    }

    // 依級／科任切換：隱藏或顯示所有節次列的班級欄位，並重算啟用狀態
    function apply_advisor_mode() {
        var advisor = is_advisor();
        $container.find('.grade-class-wrap').toggleClass('d-none', advisor);
        $container.find('.period-row').each(function () {
            var $row = $(this);
            sync_row($row, $row.find('.period-check').is(':checked'));
        });
    }

    // 回填代課老師：非「教學組派代」則切到自行覓代並填入姓名
    function set_teacher($scope, teacher) {
        if (teacher && teacher !== cfg.assign_text) {
            $scope.find('.teacher-opt[value="input"]').prop('checked', true);
            $scope.find('.teacher-input').val(teacher);
        }
    }

    // 編輯模式：把既有資料回填到卡片
    function fill_card($card, rows) {
        var first = rows[0];
        $card.find('.pay-radio[value="' + first.pay + '"]').prop('checked', true);
        $card.find('.type-radio[value="' + first.type + '"]').prop('checked', true).trigger('change');

        if (first.type === 'daily') {
            var $panel = $card.find('.daily-panel');
            set_teacher($panel, first.substitute_teacher);
            sync_row($panel, true);
        } else {
            $.each(rows, function (i, row) {
                var $row = $card.find('.period-row[data-period="' + row.class_period + '"]');
                if (!$row.length) {
                    return; // 找不到匹配的節次則略過
                }
                $row.find('.period-check').prop('checked', true);
                $row.find('.subject-input').val(row.subject);
                // 科任逐節班級：把「N年M班」拆回年級下拉與班級文字框
                var gc = /^(\d+)年(.+)班$/.exec(row.grade_class || '');
                if (gc) {
                    $row.find('.gc-grade').val(gc[1]);
                    $row.find('.gc-class').val(gc[2]);
                }
                set_teacher($row, row.substitute_teacher);
                sync_row($row, true);
            });
        }
    }

    // 依日期清單重新生成卡片；日期不變的卡片保留原本已填寫的內容
    function render_cards(dates) {
        var keep = {};
        $container.children('.substitute-card').each(function () {
            keep[$(this).attr('data-date')] = this;
        });
        var nodes = [];
        $.each(dates, function (i, date) {
            nodes.push(keep[date] || build_card(date)[0]);
        });
        $container.empty().append(nodes);
        update_copy_buttons();
    }

    // 將既有資料依日期分組，回傳 {dates: [...], map: {date: rows}}
    function group_existing(rows) {
        var map = {};
        var dates = [];
        $.each(rows, function (i, row) {
            if (!map[row.substitute_date]) {
                map[row.substitute_date] = [];
                dates.push(row.substitute_date);
            }
            map[row.substitute_date].push(row);
        });
        return { dates: dates, map: map };
    }

    // 初次載入：依起訖日期生成卡片，並回填既有資料（編輯模式）
    function init_cards() {
        var dates = (cfg.start_date && cfg.end_date && cfg.start_date <= cfg.end_date)
            ? find_work_dates(cfg.start_date, cfg.end_date)
            : [];
        var existing = group_existing(cfg.existing || []);

        // 既有資料中不在區間內的日期（例如週日補班）也要顯示
        $.each(existing.dates, function (i, date) {
            if ($.inArray(date, dates) === -1) {
                dates.push(date);
            }
        });
        dates.sort();

        $.each(dates, function (i, date) {
            var $card = build_card(date);
            $container.append($card);
            if (existing.map[date]) {
                fill_card($card, existing.map[date]);
            }
        });
        update_copy_buttons();
    }

    // 取得某範圍的代課老師值；自行覓代但未填姓名時回傳 false
    function get_teacher($scope) {
        if ($scope.find('.teacher-opt[value="input"]').is(':checked')) {
            var name = $.trim($scope.find('.teacher-input').val());
            return name === '' ? false : name;
        }
        return cfg.assign_text;
    }

    function add_hidden($box, name, value) {
        $('<input>', { type: 'hidden', name: name, value: value }).appendTo($box);
    }

    // 送出前把卡片內容序列化為隱藏欄位平行陣列；驗證失敗顯示訊息並回傳 false
    function serialize_cards() {
        var $box = $('#substitute_hidden').empty();
        var error = '';

        $container.find('.substitute-card').each(function () {
            var $card = $(this);
            var date = $card.attr('data-date');
            var pay = $card.find('.pay-radio:checked').val();
            var type = $card.find('.type-radio:checked').val();
            var rows = [];

            if (type === 'daily') {
                var teacher = get_teacher($card.find('.daily-panel'));
                if (teacher === false) {
                    error = date_label(date) + '：' + cfg.msg.no_teacher;
                    return false;
                }
                rows.push({ period: cfg.allday_text, subject: '', grade_class: '', teacher: teacher });
            } else {
                var advisor = is_advisor();
                $card.find('.period-row').each(function () {
                    var $row = $(this);
                    if (!$row.find('.period-check').is(':checked')) {
                        return;
                    }
                    var period_text = $row.attr('data-period');
                    // 科任逐節班級：年級下拉＋班級文字框合併為「N年M班」
                    var grade_class = '';
                    if (!advisor) {
                        var grade = $.trim($row.find('.gc-grade').val() || '');
                        var klass = $.trim($row.find('.gc-class').val() || '');
                        if (grade === '' || klass === '') {
                            error = date_label(date) + ' ' + period_text + '：' + cfg.msg.no_grade_class;
                            return false;
                        }
                        grade_class = grade + '年' + klass + '班';
                    }
                    var subject = $.trim($row.find('.subject-input').val());
                    if (subject === '') {
                        error = date_label(date) + ' ' + period_text + '：' + cfg.msg.no_subject;
                        return false;
                    }
                    var teacher = get_teacher($row);
                    if (teacher === false) {
                        error = date_label(date) + ' ' + period_text + '：' + cfg.msg.no_teacher;
                        return false;
                    }
                    rows.push({ period: $row.attr('data-period'), subject: subject, grade_class: grade_class, teacher: teacher });
                });
                if (!error && rows.length === 0) {
                    error = date_label(date) + '：' + cfg.msg.no_period;
                }
            }

            if (error) {
                return false;
            }

            $.each(rows, function (i, row) {
                add_hidden($box, 'substitute_date[]', date);
                add_hidden($box, 'class_period[]', row.period);
                add_hidden($box, 'subject[]', row.subject);
                add_hidden($box, 'class_grade_class[]', row.grade_class || '');
                add_hidden($box, 'substitute_teacher[]', row.teacher);
                add_hidden($box, 'pay[]', pay);
                add_hidden($box, 'type[]', type);
            });
        });

        if (error) {
            $box.empty();
            alert(error);
            return false;
        }
        return true;
    }

    function bind_events() {
        // 日薪／鐘點切換
        $container.on('change', '.type-radio', function () {
            var is_daily = $(this).val() === 'daily';
            var $card = $(this).closest('.substitute-card');
            $card.find('.daily-panel').toggleClass('d-none', !is_daily);
            $card.find('.hour-panel').toggleClass('d-none', is_daily);
        });

        // 勾選節次才開放該列欄位
        $container.on('change', '.period-check', function () {
            sync_row($(this).closest('.period-row'), this.checked);
        });

        // 教學組派代／自行覓代切換
        $container.on('change', '.teacher-opt', function () {
            var $scope = $(this).closest('.period-row, .daily-panel');
            var enabled = $scope.hasClass('period-row') ? $scope.find('.period-check').is(':checked') : true;
            sync_row($scope, enabled);
        });

        // 「同第一天」按鈕：複製第一張卡片的科目與代課老師
        $container.on('click', '.copy-first-day-btn', function (e) {
            e.preventDefault();
            var $card = $(this).closest('.substitute-card');
            copy_from_first_card($card);
        });
    }

    function bind_submit() {
        $('#myForm').on('submit', function () {
            // 合併年級＋班級寫入 grade_class
            var grade = $('#grade').val() || '';
            var classroom = $('#classroom').val() || '';
            $('#grade_class').val(grade && classroom ? grade + '年' + classroom + '班' : '');

            return serialize_cards();
        });
    }

    // My97DatePicker 的 onpicked 回呼（需為全域函式），僅新增模式會觸發
    window.checkDates = function () {
        var start = $('#start_date').val();
        var end = $('#end_date').val();
        if (!start || !end) {
            return;
        }
        if (start > end) {
            alert(cfg.msg.date_order);
            $('#start_date').val('');
            $('#end_date').val('');
            $container.empty();
            return;
        }
        cfg.start_date = start;
        cfg.end_date = end;
        render_cards(find_work_dates(start, end));
        apply_advisor_mode();
    };

    $(function () {
        $container = $('#substitute_container');
        if (!$container.length) {
            return;
        }
        cfg = window.LEAVE_FORM || {};

        bind_events();
        bind_submit();

        // 選科任時清空年級班級下拉，並切換逐節班級欄位顯示
        $('input[name="is_advisor"]').on('change', function () {
            if ($(this).val() === '0') {
                $('#grade').val('');
                $('#classroom').val('');
            }
            apply_advisor_mode();
        });

        init_cards();
        apply_advisor_mode();
    });
})(jQuery);
