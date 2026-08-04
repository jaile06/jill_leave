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
        var $swap_panel = $card.find('.swap-panel');

        // 在節次面板頂部加入顯眼的勾選提示 Banner（使用 FA4/5/6 完全相容圖示類別）
        if (cfg.check_period_tip) {
            var tip_html = '<div class="alert alert-primary py-1 px-2 mb-2 small fw-bold"><i class="fa fa-info-circle me-1"></i><i class="fa fa-check-square-o me-1"></i> ' + cfg.check_period_tip + '</div>';
            $hour_panel.append(tip_html);
            $swap_panel.append(tip_html);
        }

        $.each(cfg.periods || [], function (i, p) {
            var $row = $($.trim($('#period_row_tpl').html()));
            $row.attr('data-period', p);
            $row.find('.period-text').text(p);
            $row.find('.teacher-opt').attr('name', 'ui_teacher_h_' + seq + '_' + i);
            $hour_panel.append($row);

            var $srow = $($.trim($('#swap_period_row_tpl').html()));
            $srow.attr('data-period', p);
            $srow.find('.period-text').text(p);
            $swap_panel.append($srow);
        });

        // 補調課單模式：整張單固定為補調課，不需選擇調代課類型與支付方式
        // 卡片此時尚未插入 DOM，change 事件委派抓不到，直接呼叫 apply_type
        if (cfg.is_swap) {
            $card.find('.type-select-wrap').addClass('d-none');
            $card.find('.type-radio[value="swap"]').prop('checked', true);
            apply_type($card, 'swap');
        }
        apply_pay_lock($card);
        return $card;
    }

    // 依假別（cate_sn）鎖定支付方式：假別設有 force_pay 時，卡片的支付方式固定並停用切換
    function apply_pay_lock($card) {
        var lock = cfg.force_pay || '';
        var $radios = $card.find('.pay-radio');
        if (lock) {
            $radios.filter('[value="' + lock + '"]').prop('checked', true);
        }
        $radios.prop('disabled', !!lock);
    }

    // 假別切換時，重新讀取該假別的 force_pay 並套用到所有卡片
    function update_pay_lock() {
        var $opt = $('#cate_sn').find('option:selected');
        cfg.force_pay = $opt.data('forcePay') || '';
        $container.children('.substitute-card').each(function () {
            apply_pay_lock($(this));
        });
    }

    // 依調代課類型切換面板顯示與支付方式欄位（型別 change 事件與程式強制設定共用）
    function apply_type($card, val) {
        $card.find('.daily-panel').toggleClass('d-none', val !== 'daily');
        $card.find('.hour-panel').toggleClass('d-none', val !== 'hour');
        $card.find('.swap-panel').toggleClass('d-none', val !== 'swap');
        // 補調課不涉及代課鐘點費，隱藏支付方式
        $card.find('.pay-method-wrap').toggleClass('d-none', val === 'swap');
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
            var panel_class = first_type === 'swap' ? '.swap-panel' : '.hour-panel';

            // 逐節次複製：目標節次沿用來源同節次自己的勾選與內容，不同節次各自獨立
            $first_card.find(panel_class + ' .period-row').each(function () {
                var $src = $(this);
                var period = $src.attr('data-period');
                var $dst = $target_card.find(panel_class + ' .period-row[data-period="' + period + '"]');
                if (!$dst.length) { return; }

                var checked = $src.find('.period-check').is(':checked');
                if (checked) {
                    $dst.find('.period-check').prop('checked', true);
                    var subject = $.trim($src.find('.subject-input').val());
                    if (subject !== '') {
                        $dst.find('.subject-input').val(subject);
                    }
                    var teacher_mode = $src.find('.teacher-opt:checked').val() || 'assign';
                    $dst.find('.teacher-opt[value="' + teacher_mode + '"]').prop('checked', true);
                    $dst.find('.teacher-input').val($src.find('.teacher-input').val());
                    $dst.find('.handle-select').val($src.find('.handle-select').val() || (first_type === 'swap' ? 'swap' : 'substitute'));
                }
                sync_row($dst, $dst.find('.period-check').is(':checked'));
            });
        }
    }

    // 目前是否為級任（科任才需逐節填班級）
    function is_advisor() {
        return $('input[name="is_advisor"]:checked').val() === '1';
    }

    // 依勾選狀態啟用／停用某範圍（節次列或日薪區）的欄位
    function sync_row($scope, enabled) {
        $scope.find('.subject-input, .teacher-opt, .handle-select').prop('disabled', !enabled);
        // 逐節班級欄位僅科任且該節勾選時開放
        $scope.find('.gc-grade, .gc-class').prop('disabled', !enabled || is_advisor());
        var use_input = $scope.find('.teacher-opt[value="input"]').is(':checked');
        $scope.find('.teacher-input').prop('disabled', !enabled || !use_input);
        if ($scope.hasClass('period-row')) {
            $scope.toggleClass('period-row-active', enabled);
            apply_handle($scope);
        }
    }

    // 依遺課處理方式切換欄位：
    // 委託代課→代課老師；自己補課→僅異動後日期節次；與他人調課→異動後＋對調科目＋對調教師
    function apply_handle($row) {
        var handle = $row.find('.handle-select').val() || 'substitute';
        var checked = $row.find('.period-check').is(':checked');
        $row.find('.changed-wrap').toggleClass('d-none', handle === 'substitute');
        $row.find('.swap-subject-wrap').toggleClass('d-none', handle !== 'swap');
        // 調課／補課無「教學組派代」概念，直接填對方姓名
        $row.find('.teacher-opt-wrap').toggleClass('d-none', handle !== 'substitute');
        $row.find('.teacher-name-wrap').toggleClass('d-none', handle === 'makeup');
        $row.find('.teacher-input').attr('placeholder', handle === 'swap' ? cfg.swap_teacher_text : cfg.teacher_text);
        $row.find('.changed-date, .changed-period, .swap-subject-input').prop('disabled', !checked || handle === 'substitute');
        if (handle === 'swap') {
            $row.find('.teacher-input').prop('disabled', !checked);
        }
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
            var panel_class = first.type === 'swap' ? '.swap-panel' : '.hour-panel';
            $.each(rows, function (i, row) {
                var $row = $card.find(panel_class + ' .period-row[data-period="' + row.class_period + '"]');
                if (!$row.length) {
                    return; // 找不到匹配的節次則略過
                }
                $row.find('.period-check').prop('checked', true);
                $row.find('.subject-input').val(row.subject);
                // 處理方式與異動後課程
                $row.find('.handle-select').val(row.handle || (first.type === 'swap' ? 'swap' : 'substitute'));
                $row.find('.changed-date').val(row.swap_date || '');
                $row.find('.changed-period').val(row.swap_period || '');
                $row.find('.swap-subject-input').val(row.swap_subject || '');
                if (row.handle === 'swap') {
                    $row.find('.teacher-input').val(row.substitute_teacher || '');
                }
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

        // 既有資料中在區間內但被跳過的日期（例如週日補班）也要顯示；區間外的殘留資料不顯示
        $.each(existing.dates, function (i, date) {
            if ($.inArray(date, dates) === -1 && date >= cfg.start_date && date <= cfg.end_date) {
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
                rows.push({ period: cfg.allday_text, subject: '', grade_class: '', teacher: teacher, handle: 'substitute' });
            } else {
                var panel_class = type === 'swap' ? '.swap-panel' : '.hour-panel';
                var advisor = is_advisor();
                $card.find(panel_class + ' .period-row').each(function () {
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
                    // 遺課處理方式：代課要代課老師；補課／調課要異動後日期與節次，調課另需對調教師
                    var handle = $row.find('.handle-select').val() || (type === 'swap' ? 'swap' : 'substitute');
                    var changed = { date: '', period: '', subject: '' };
                    var teacher = '';
                    if (handle === 'substitute') {
                        teacher = get_teacher($row);
                        if (teacher === false) {
                            error = date_label(date) + ' ' + period_text + '：' + cfg.msg.no_teacher;
                            return false;
                        }
                    } else {
                        changed.date = $.trim($row.find('.changed-date').val() || '');
                        changed.period = $row.find('.changed-period').val() || '';
                        changed.subject = $.trim($row.find('.swap-subject-input').val() || '');
                        // 對調／補課：異動後日期與節次必填
                        if (changed.date === '' || changed.period === '') {
                            error = date_label(date) + ' ' + period_text + '：' + cfg.msg.no_changed;
                            return false;
                        }
                        if (handle === 'swap') {
                            // 對調科目必填
                            if (changed.subject === '') {
                                error = date_label(date) + ' ' + period_text + '：' + cfg.msg.no_swap_subject;
                                return false;
                            }
                            // 對調老師必填
                            teacher = $.trim($row.find('.teacher-input').val() || '');
                            if (teacher === '') {
                                error = date_label(date) + ' ' + period_text + '：' + cfg.msg.no_swap_teacher;
                                return false;
                            }
                        }
                    }
                    rows.push({ period: period_text, subject: subject, grade_class: grade_class, teacher: teacher, handle: handle, changed: changed });
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
                add_hidden($box, 'handle[]', row.handle || 'substitute');
                add_hidden($box, 'swap_date[]', (row.changed || {}).date || '');
                add_hidden($box, 'swap_period[]', (row.changed || {}).period || '');
                add_hidden($box, 'swap_subject[]', (row.changed || {}).subject || '');
            });
        });

        if (error) {
            $box.empty();
            alert(error);
            return false;
        }

        // 跨卡片衝堂檢查：對調/補課的「異動後日期＋節次」不得重複
        var seen_periods = {};
        var conflict_msg = '';
        $box.find('input[name="swap_date[]"]').each(function (i) {
            var sw_date = $(this).val();
            var sw_period = $box.find('input[name="swap_period[]"]').eq(i).val();
            if (!sw_date || !sw_period) { return; }
            var key = sw_date + '|' + sw_period;
            if (seen_periods[key]) {
                conflict_msg = cfg.msg.conflict_period
                    .replace('{date}', sw_date)
                    .replace('{period}', sw_period);
                return false; // break each
            }
            seen_periods[key] = true;
        });
        if (conflict_msg) {
            $box.empty();
            alert(conflict_msg);
            return false;
        }

        return true;
    }

    function bind_events() {
        // 調代課類型切換 (daily / hour / swap)
        $container.on('change', '.type-radio', function () {
            apply_type($(this).closest('.substitute-card'), $(this).val());
        });

        // 勾選節次才開放該列欄位
        $container.on('change', '.period-check', function () {
            sync_row($(this).closest('.period-row'), this.checked);
        });

        // 遺課處理方式切換（代課／補課／調課）
        $container.on('change', '.handle-select', function () {
            apply_handle($(this).closest('.period-row'));
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

    // 從已序列化的隱藏欄位中收集所有補調課的異動後日期＋節次
    function collect_swap_slots() {
        var $box = $('#substitute_hidden');
        var slots = [];
        $box.find('input[name="swap_date[]"]').each(function (i) {
            var sw_date = $(this).val();
            var sw_period = $box.find('input[name="swap_period[]"]').eq(i).val();
            if (sw_date && sw_period) {
                slots.push(sw_date + '|' + sw_period);
            }
        });
        return slots;
    }

    // AJAX 查詢異動後節次是否與同一人的鐘點請假節次衝突
    function check_hour_conflict(callback) {
        var slots = collect_swap_slots();
        // 沒有補調課資料，直接通過
        if (slots.length === 0 || !cfg.ajax_url) {
            callback(true);
            return;
        }

        var post_data = {
            op: 'check_hour_conflict',
            exclude_sn: cfg.exclude_sn || 0
        };
        // jQuery $.post 傳陣列的方式
        $.each(slots, function (i, slot) {
            post_data['swap_slots[' + i + ']'] = slot;
        });

        $.post(cfg.ajax_url, post_data, function (resp) {
            if (resp && resp.conflicts && resp.conflicts.length > 0) {
                // 組裝衝突訊息並告警
                var msgs = [];
                $.each(resp.conflicts, function (i, c) {
                    msgs.push(cfg.msg.hour_conflict
                        .replace('{date}', c.date)
                        .replace('{period}', c.period)
                        .replace('{sn}', c.sn));
                });
                alert(msgs.join('\n'));
                callback(false);
            } else {
                callback(true);
            }
        }, 'json').fail(function () {
            // AJAX 失敗時不阻擋送出（降級處理，後端仍有二次驗證）
            callback(true);
        });
    }

    var submitting = false; // 防止重複送出

    function bind_submit() {
        $('#myForm').on('submit', function (e) {
            // 合併年級＋班級寫入 grade_class
            var grade = $('#grade').val() || '';
            var classroom = $('#classroom').val() || '';
            $('#grade_class').val(grade && classroom ? grade + '年' + classroom + '班' : '');

            // 若已通過衝突檢查（submitting = true），直接放行
            if (submitting) {
                return true;
            }

            // 同步驗證（欄位必填、跨卡片衝堂）
            if (!serialize_cards()) {
                return false;
            }

            // 非同步 AJAX 衝突檢查
            e.preventDefault();
            var $form = $(this);
            var $btn = $form.find('button[type="submit"]');
            $btn.prop('disabled', true);

            check_hour_conflict(function (pass) {
                if (pass) {
                    submitting = true;
                    $form[0].submit(); // 原生 submit，不再觸發 jQuery handler
                } else {
                    // 衝突→清空隱藏欄位，讓使用者修改
                    $('#substitute_hidden').empty();
                    $btn.prop('disabled', false);
                }
            });

            return false;
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

        // 手動輸入日期（未經 My97 onpicked）也要連動重算卡片
        $('#start_date, #end_date').on('change', window.checkDates);

        // 假別切換時連動鎖定支付方式（例如身心調適假固定為公費）
        $('#cate_sn').on('change', update_pay_lock);

        // 在輸入欄按 Enter 不隱式送出表單，改為觸發 change（日期欄會連動重算卡片）
        $('#myForm').on('keydown', 'input', function (e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                $(this).trigger('change');
            }
        });

        // 選科任時清空年級班級下拉，並切換逐節班級欄位顯示
        $('input[name="is_advisor"]').on('change', function () {
            if ($(this).val() === '0') {
                $('#grade').val('');
                $('#classroom').val('');
            }
            apply_advisor_mode();
        });

        update_pay_lock();
        init_cards();
        update_pay_lock(); // fill_card 可能回填舊資料的支付方式，鎖定假別需再次強制套用
        apply_advisor_mode();
    });
})(jQuery);
