<?php
use Xmf\Request;
use XoopsModules\Jill_leave\Tools;
use XoopsModules\Jill_leave\Jill_leave;
use XoopsModules\Jill_leave\Jill_leave_cate;
use XoopsModules\Jill_leave\Jill_leave_substitute;

/*-----------引入檔案區--------------*/
require_once __DIR__ . '/header.php';

// 屏蔽所有警告與非致命錯誤，以防污染 PDF 二進位資料流
error_reporting(0);
ini_set('display_errors', 0);

// 僅登入使用者才可以匯出 PDF
if (empty($_SESSION['now_user'])) {
    redirect_header(XOOPS_URL, 3, "請先登入");
    exit;
}

$sn = Request::getInt('sn');
if (empty($sn)) {
    redirect_header($_SERVER['HTTP_REFERER'], 3, "無此假單編號");
    exit;
}

// filter='' 取原始未轉義字串：PDF 走 TCPDF Cell() 純文字輸出，非 HTML 渲染，
// 若用預設 htmlSpecialChars 過的資料，含 & ' 等符號的姓名會顯示成 &amp; 字面值
$jill_leave = Jill_leave::get(['sn' => $sn], [], '');
if (empty($jill_leave)) {
    redirect_header($_SERVER['HTTP_REFERER'], 3, "無此請假資料");
    exit;
}

Tools::chk_own($jill_leave['uid']);

$jill_leave_cate = Jill_leave_cate::get(['cate_sn' => $jill_leave['cate_sn']], [], '');
$cate_title = $jill_leave_cate['cate_title'] ?? '';

// 取得代課明細（已含 display_class 解析；escape=false 同上，避免 PDF 顯示雙重轉義）
$substitutes = Jill_leave_substitute::get_all_by_leave($sn, false);

// 展開為逐列陣列，方便後續迭代
$rows = [];
foreach ($substitutes as $sub) {
    $pay_text  = ($sub['pay'] == 'school') ? '公費' : '自費';
    $type_text = match ($sub['type']) { 'hour' => '鐘點', 'swap' => '補調課', default => '日薪' };
    if (!empty($sub['classes'])) {
        foreach ($sub['classes'] as $cls) {
            $gc = ($cls['grade_class'] ?? '') !== '' ? $cls['grade_class'] : ($jill_leave['grade_class'] ?? '');
            $rows[] = [
                'date'     => $sub['substitute_date'],
                'period'   => $cls['class_period'],
                'gc'       => $gc,
                'subject'  => $cls['subject'],
                'handle'   => $cls['handle'] ?? 'substitute',
                'handle_text' => $cls['handle_text'] ?? '',
                'swap_date'   => $cls['swap_date'] ?? '',
                'swap_period' => $cls['swap_period'] ?? '',
                'swap_subject'=> $cls['swap_subject'] ?? '',
                'teacher'  => $cls['substitute_teacher'] ?? '',
                'pay'      => $pay_text,
                'type'     => $type_text,
            ];
        }
    } else {
        $rows[] = [
            'date' => $sub['substitute_date'], 'period' => '', 'gc' => '',
            'subject' => '', 'handle' => 'substitute', 'handle_text' => '',
            'swap_date' => '', 'swap_period' => '', 'swap_subject' => '',
            'teacher' => '', 'pay' => $pay_text, 'type' => $type_text,
        ];
    }
}

// ── 載入 TCPDF ─────────────────────────────────
require_once XOOPS_ROOT_PATH . "/modules/tadtools/tcpdf/tcpdf.php";

// 直向 A4（Portrait）
$pdf = new TCPDF("P", PDF_UNIT, "A4", true, 'UTF-8', false);
$pdf->setPrintHeader(false);
$pdf->setPrintFooter(false);
$pdf->SetMargins(12, 12, 12);
$pdf->SetAutoPageBreak(true, 12);
$pdf->AddPage();

// 字型設定
$font = file_exists(XOOPS_ROOT_PATH . '/modules/tadtools/tcpdf/fonts/twkai98_1.php')
    ? 'twkai98_1' : 'msungstdlight';

// 可用頁寬 = 210 - 12*2 = 186 mm
$W = 186;

// ══════════════════════════════════════════
// 標題區
// ══════════════════════════════════════════
$pdf->SetFont($font, 'B', 14);
$pdf->Cell($W, 8, "教師請假所遺課程處理報告單", 0, 1, 'C');
$pdf->SetFont($font, '', 8);
$pdf->Cell($W, 5, "★ 遺課處理單請交給 教務處，差勤系統的假單上也務必上傳本表單 ★", 0, 1, 'C');
$pdf->Ln(2);

// ══════════════════════════════════════════
// 一、基本資料
// ══════════════════════════════════════════
$pdf->SetFont($font, 'B', 10);
$pdf->Cell($W, 6, "一、基本資料", 0, 1, 'L');
$pdf->SetFont($font, '', 9);

$is_advisor_text = $jill_leave['is_advisor'] ? "導師" : "科任";
$grade_class_main = $jill_leave['grade_class'] ?? '';

// 行1：職稱、姓名、職務代理人
$pdf->Cell(30, 7, "職稱：{$is_advisor_text}", 1, 0, 'L');
$pdf->Cell(54, 7, "姓名：{$jill_leave['leavers']}", 1, 0, 'L');
$pdf->Cell(50, 7, "職務代理人：", 1, 0, 'L');
$pdf->Cell($W - 134, 7, "公假字號：", 1, 1, 'L');

// 行2：假別、請假日數
$pdf->Cell(30, 7, "假別：{$cate_title}", 1, 0, 'L');
$pdf->Cell($W - 30, 7, "請假日期：{$jill_leave['start_date']} 至 {$jill_leave['end_date']}", 1, 1, 'L');

// ══════════════════════════════════════════
// 二、遺課處理表格
// ══════════════════════════════════════════
$pdf->SetFont($font, 'B', 10);
$pdf->Cell($W, 6, "二、請假期間課程與遺課處理方式", 0, 1, 'L');

// ── 表頭設計（對應紙本欄位）────────────────────
// 欄寬規劃（合計 186）：
// 日(9) 星(9) 節(10) 科目(18) 班(16) | 代課方式(30) | 補課日(16)+節(10) | 調課日(16)+節(10)+對調師(18)+科目(14) | 備註(10)
// 簡化版：請假課程 5欄 + 1委代課 + 2補課 + 4調課 + 備註

// 欄寬定義
$c_date    = 14; // 日期（月/日）
$c_week    = 10; // 星期
$c_period  = 12; // 節次
$c_subj    = 18; // 科目
$c_class   = 16; // 班級
// → 小計 70

$c_sub_pay = 16; // 代課方式（公/自費）
$c_sub_tch = 24; // 代課老師
// → 代課小計 40

$c_mk_date = 16; // 補課日
$c_mk_per  = 14; // 補課節次
// → 補課小計 30

$c_sw_date = 16; // 調課日
$c_sw_per  = 12; // 調課節次
$c_sw_tch  = 18; // 對調教師
// → 調課小計 46 (調課日/節/師)

// 總計目前：70+40+30+46=186 ✓（備註合進去）

// ─ 掃描實際使用的處理模式 ─────────────────
$has_substitute = false;
$has_makeup     = false;
$has_swap       = false;
foreach ($rows as $r) {
    if ($r['handle'] === 'substitute') $has_substitute = true;
    elseif ($r['handle'] === 'makeup')  $has_makeup     = true;
    elseif ($r['handle'] === 'swap')    $has_swap       = true;
}
// 若全部空，保留全部（避免無欄）
if (!$has_substitute && !$has_makeup && !$has_swap) {
    $has_substitute = $has_makeup = $has_swap = true;
}

// ─ 依啟用模式重新分配右側欄寬，填滿整頁 ──────────
$left_w     = $c_date + $c_week + $c_period + $c_subj + $c_class; // 70
$right_avail = $W - $left_w;                                        // 116

// 原始右側總寬
$right_orig = 0;
if ($has_substitute) $right_orig += $c_sub_pay + $c_sub_tch;
if ($has_makeup)     $right_orig += $c_mk_date + $c_mk_per;
if ($has_swap)       $right_orig += $c_sw_date + $c_sw_per + $c_sw_tch;

if ($right_orig > 0 && $right_orig !== $right_avail) {
    $scale = $right_avail / $right_orig;
    if ($has_substitute) {
        $c_sub_pay = (int)round($c_sub_pay * $scale);
        $c_sub_tch = (int)round($c_sub_tch * $scale);
    }
    if ($has_makeup) {
        $c_mk_date = (int)round($c_mk_date * $scale);
        $c_mk_per  = (int)round($c_mk_per  * $scale);
    }
    if ($has_swap) {
        $c_sw_date = (int)round($c_sw_date * $scale);
        $c_sw_per  = (int)round($c_sw_per  * $scale);
        $c_sw_tch  = (int)round($c_sw_tch  * $scale);
    }
    // 修正捨入誤差：補到最後一個欄位
    $actual_right = 0;
    if ($has_substitute) $actual_right += $c_sub_pay + $c_sub_tch;
    if ($has_makeup)     $actual_right += $c_mk_date + $c_mk_per;
    if ($has_swap)       $actual_right += $c_sw_date + $c_sw_per + $c_sw_tch;
    $diff = $right_avail - $actual_right;
    if ($has_swap)           $c_sw_tch  += $diff;
    elseif ($has_makeup)     $c_mk_per  += $diff;
    else                     $c_sub_tch += $diff;
}

// ─ 第一層標頭（分組大標）─────────────────
$pdf->SetFont($font, 'B', 7);
$h1 = 5;
// 請假期間課程（跨5欄）
$pdf->Cell($c_date + $c_week + $c_period + $c_subj + $c_class, $h1, "請假期間課程", 1, 0, 'C');
// 1.委託他人代課（僅在有 substitute 時顯示）
if ($has_substitute) {
    $pdf->Cell($c_sub_pay + $c_sub_tch, $h1, "1. 委託他人代課方式", 1, 0, 'C');
}
// 2.自己補課（僅在有 makeup 時顯示）
if ($has_makeup) {
    $pdf->Cell($c_mk_date + $c_mk_per, $h1, "2. 已補課方式", 1, 0, 'C');
}
// 3.與他人調課（僅在有 swap 時顯示）
if ($has_swap) {
    $pdf->Cell($c_sw_date + $c_sw_per + $c_sw_tch, $h1, "3. 本人調課方式", 1, 0, 'C');
}
$pdf->Ln();

// ─ 第二層標頭（欄位名稱）─────────────────
$h2 = 7;
$pdf->Cell($c_date,   $h2, "月/日",   1, 0, 'C');
$pdf->Cell($c_week,   $h2, "星期",    1, 0, 'C');
$pdf->Cell($c_period, $h2, "節次",    1, 0, 'C');
$pdf->Cell($c_subj,   $h2, "科目",    1, 0, 'C');
$pdf->Cell($c_class,  $h2, "班級",    1, 0, 'C');
if ($has_substitute) {
    $pdf->Cell($c_sub_pay,$h2, "公/自費", 1, 0, 'C');
    $pdf->Cell($c_sub_tch,$h2, "代課老師",1, 0, 'C');
}
if ($has_makeup) {
    $pdf->Cell($c_mk_date,$h2, "補課日期",1, 0, 'C');
    $pdf->Cell($c_mk_per, $h2, "節次班",  1, 0, 'C');
}
if ($has_swap) {
    $pdf->Cell($c_sw_date,$h2, "調課日期",1, 0, 'C');
    $pdf->Cell($c_sw_per, $h2, "節次班",  1, 0, 'C');
    $pdf->Cell($c_sw_tch, $h2, "對調教師/科目", 1, 0, 'C');
}
$pdf->Ln();

// ─ 資料列 ────────────────────────────────
$pdf->SetFont($font, '', 8);
$row_h = 7;

if (!empty($rows)) {
    foreach ($rows as $r) {
        // 月/日 從 substitute_date (yyyy-mm-dd) 取
        $md = '';
        if (!empty($r['date'])) {
            $parts = explode('-', $r['date']);
            $md = ((int)($parts[1] ?? 0)) . '/' . ((int)($parts[2] ?? 0));
        }
        // 星期
        $weekdays = ['日','一','二','三','四','五','六'];
        $wday = !empty($r['date']) ? $weekdays[(int)date('w', strtotime($r['date']))] : '';

        // 依處理方式決定欄位填入
        $sub_pay = '';
        $sub_tch = '';
        $mk_date = '';
        $mk_per  = '';
        $sw_date = '';
        $sw_per  = '';
        $sw_tch  = '';

        if ($r['handle'] === 'substitute') {
            $sub_pay = $r['pay'];
            $sub_tch = $r['teacher'];
        } elseif ($r['handle'] === 'makeup') {
            $mk_date = $r['swap_date'];
            $mk_per  = $r['swap_period'];
        } elseif ($r['handle'] === 'swap') {
            $sw_date = $r['swap_date'];
            $sw_per  = $r['swap_period'];
            $sw_tch  = $r['teacher'];
            if (!empty($r['swap_subject'])) {
                $sw_tch .= '/' . $r['swap_subject'];
            }
        }

        $pdf->Cell($c_date,   $row_h, $md,          1, 0, 'C');
        $pdf->Cell($c_week,   $row_h, $wday,         1, 0, 'C');
        $pdf->Cell($c_period, $row_h, $r['period'],  1, 0, 'C');
        $pdf->Cell($c_subj,   $row_h, $r['subject'], 1, 0, 'C');
        $pdf->Cell($c_class,  $row_h, $r['gc'],      1, 0, 'C');
        if ($has_substitute) {
            $pdf->Cell($c_sub_pay,$row_h, $sub_pay, 1, 0, 'C');
            $pdf->Cell($c_sub_tch,$row_h, $sub_tch, 1, 0, 'C');
        }
        if ($has_makeup) {
            $pdf->Cell($c_mk_date,$row_h, $mk_date, 1, 0, 'C');
            $pdf->Cell($c_mk_per, $row_h, $mk_per,  1, 0, 'C');
        }
        if ($has_swap) {
            $pdf->Cell($c_sw_date,$row_h, $sw_date, 1, 0, 'C');
            $pdf->Cell($c_sw_per, $row_h, $sw_per,  1, 0, 'C');
            $pdf->Cell($c_sw_tch, $row_h, $sw_tch,  1, 0, 'C');
        }
        $pdf->Ln();
    }
} else {
    $pdf->Cell($W, $row_h, "無遺課資料", 1, 1, 'C');
}

$pdf->Ln(4);

// ══════════════════════════════════════════
// 注意事項
// ══════════════════════════════════════════
$pdf->SetFont($font, '', 7);
$notes = [
    "1. 與他人調課：調課教師需雙方同意，並填寫調課日期、節次及對調教師。",
    "2. 遺課處理單紙本經核章後請交給教務處，請將PDF檔上傳至差勤系統。",
];
foreach ($notes as $note) {
    $pdf->Cell($W, 5, $note, 0, 1, 'L');
}
$pdf->Ln(3);

// ══════════════════════════════════════════
// 核章欄
// ══════════════════════════════════════════
$pdf->SetFont($font, 'B', 9);
$pdf->Cell($W, 6, "核章", 0, 1, 'L');

$sign_titles = [_MD_JILLLEAVE_SIGN_TEACHER, _MD_JILLLEAVE_SIGN_GROUP_LEADER, _MD_JILLLEAVE_SIGN_DIRECTOR];
$sign_w = $W / count($sign_titles);
$pdf->SetFont($font, '', 9);
foreach ($sign_titles as $i => $title) {
    $pdf->Cell($sign_w, 7, $title, 1, ($i === count($sign_titles) - 1) ? 1 : 0, 'C');
}
foreach ($sign_titles as $i => $title) {
    $pdf->Cell($sign_w, 22, '', 1, ($i === count($sign_titles) - 1) ? 1 : 0, 'C');
}

$pdf->Ln(4);
$pdf->SetFont($font, '', 7);
$pdf->Cell($W, 5, "列印日期：" . date("Y-m-d H:i:s"), 0, 1, 'R');

// 輸出 PDF
ob_end_clean();
$pdf->Output("leave_sheet_{$sn}.pdf", "D");
