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


// 僅登入使用者才可以匯出 PDF（Tools::chk_own 內有權限檢查，一般使用者只能下載自己的假單，管理員可下載全部）
if (empty($_SESSION['now_user'])) {
    redirect_header(XOOPS_URL, 3, "請先登入");
    exit;
}

$sn = Request::getInt('sn');
if (empty($sn)) {
    redirect_header($_SERVER['HTTP_REFERER'], 3, "無此假單編號");
    exit;
}

// 取得請假單主檔
$jill_leave = Jill_leave::get(['sn' => $sn]);
if (empty($jill_leave)) {
    redirect_header($_SERVER['HTTP_REFERER'], 3, "無此請假資料");
    exit;
}

// 檢查是否是管理者或資料擁有者
Tools::chk_own($jill_leave['uid']);

// 取得假別名稱
$jill_leave_cate = Jill_leave_cate::get(['cate_sn' => $jill_leave['cate_sn']]);
$cate_title = $jill_leave_cate['cate_title'] ?? '';

// 取得代課明細
$substitutes = Jill_leave_substitute::get_all_by_leave($sn);

// 載入 TCPDF
require_once XOOPS_ROOT_PATH . "/modules/tadtools/tcpdf/tcpdf.php";

// 實體化 PDF 物件 (橫式 L)
$pdf = new TCPDF("L", PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
$pdf->setPrintHeader(false); //不要頁首
$pdf->setPrintFooter(false); //不要頁尾
$pdf->SetMargins(10, 10, 10);
$pdf->SetHeaderMargin(false);
$pdf->SetFooterMargin(false);
$pdf->SetAutoPageBreak(true, 10); //設定自動分頁

$pdf->AddPage();

// 設定字型 (twkai98_1 為 Tadtools tcpdf 內建字型)
if (file_exists(XOOPS_ROOT_PATH . '/modules/tadtools/tcpdf/fonts/twkai98_1.php')) {
    $pdf->SetFont('twkai98_1', 'B', 16, '', true);
} else {
    $pdf->SetFont('msungstdlight', 'B', 16, '', true); // 備用字型
}

// 標題
$pdf->Cell(277, 12, "教師請假單 ({$jill_leave['leavers']})", 0, 1, 'C');
$pdf->Ln(4);

// 個人與請假基本資訊
if (file_exists(XOOPS_ROOT_PATH . '/modules/tadtools/tcpdf/fonts/twkai98_1.php')) {
    $pdf->SetFont('twkai98_1', '', 12, '', true);
} else {
    $pdf->SetFont('msungstdlight', '', 12, '', true);
}

$is_advisor_text = $jill_leave['is_advisor'] ? "導師 ({$jill_leave['grade_class']})" : "科任";

$pdf->Cell(60, 10, "請假者：{$jill_leave['leavers']}", 1, 0, 'L');
$pdf->Cell(60, 10, "職稱：{$is_advisor_text}", 1, 0, 'L');
$pdf->Cell(157, 10, "假別：{$cate_title}", 1, 1, 'L');

$pdf->Cell(277, 10, "請假時間：{$jill_leave['start_date']} 至 {$jill_leave['end_date']}", 1, 1, 'L');

$pdf->Ln(4);

// 代課資訊標題
$pdf->Cell(277, 10, "代課與派代明細", 0, 1, 'L');

// 欄位標頭
$pdf->Cell(47, 8, "代課日期", 1, 0, 'C');
$pdf->Cell(30, 8, "支付方式", 1, 0, 'C');
$pdf->Cell(30, 8, "代課類型", 1, 0, 'C');
$pdf->Cell(170, 8, "代課節次 / 科目 / 代課老師", 1, 1, 'C');

// 欄位內容
if (!empty($substitutes)) {
    foreach ($substitutes as $substitute) {
        $pay_text = ($substitute['pay'] == 'school') ? "公費派代" : "自費代課";
        $type_text = ($substitute['type'] == 'hour') ? "鐘點" : "日薪";

        // 計算高度（若為鐘點且有多節課，需動態算高度）
        $class_count = count($substitute['classes']);
        $row_height = ($class_count > 0) ? $class_count * 8 : 10;

        // 代課日期
        $pdf->MultiCell(47, $row_height, $substitute['substitute_date'], 1, 'C', false, 0);
        // 支付方式
        $pdf->MultiCell(30, $row_height, $pay_text, 1, 'C', false, 0);
        // 代課類型
        $pdf->MultiCell(30, $row_height, $type_text, 1, 'C', false, 0);

        // 代課資訊明細
        $detail_text = '';
        if ($substitute['type'] == 'daily') {
            // 日薪通常就一筆老師
            $teacher = $substitute['classes'][0]['substitute_teacher'] ?? '無';
            $detail_text = "全日代課 (代課老師：{$teacher})";
        } else {
            // 鐘點，逐節列出
            $lines = [];
            foreach ($substitute['classes'] as $cls) {
                $cls_period_name = sprintf("第%s節", $cls['class_period']);
                $lines[] = "{$cls_period_name}：[{$cls['subject']}] {$cls['substitute_teacher']}";
            }
            $detail_text = implode("\n", $lines);
        }

        $pdf->MultiCell(170, $row_height, $detail_text, 1, 'L', false, 1);
    }
} else {
    $pdf->Cell(277, 10, "無代課資訊", 1, 1, 'C');
}

$pdf->Ln(6);
$pdf->Cell(277, 6, "列印日期：" . date("Y-m-d H:i:s"), 0, 1, 'R');

// 輸出 PDF
ob_end_clean();
$pdf->Output("leave_sheet_{$sn}.pdf", "D");
