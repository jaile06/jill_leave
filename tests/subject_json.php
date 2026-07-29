<?php
// subject 欄位 JSON 進出的自我檢查（遺課處理方式塞在同一欄，不動資料表結構）
// 執行：php tests/subject_json.php
define('_MD_JILLLEAVE_HANDLE_SUBSTITUTE', '委託代課');
define('_MD_JILLLEAVE_HANDLE_MAKEUP', '自己補課');
define('_MD_JILLLEAVE_HANDLE_SWAP', '與他人調課');

require __DIR__ . '/../class/Jill_leave_class.php';

use XoopsModules\Jill_leave\Jill_leave_class as C;

//代課列：JSON 與舊資料格式一致，不多出 handle 鍵
$raw = C::encode_subject('3年2班', '數學', ['handle' => 'substitute']);
assert($raw === '{"grade_class":"3年2班","subject":"數學"}', '代課列不應寫入 handle');

//舊資料（純文字）與舊 JSON 皆解得回來，且預設為委託代課
assert(C::decode_subject('國語')['subject'] === '國語');
assert(C::decode_subject('國語')['handle'] === 'substitute');
assert(C::decode_subject($raw)['grade_class'] === '3年2班');
assert(C::decode_subject($raw)['handle'] === 'substitute');

//調課列：異動後日期／節次／對調科目來回不失真
$swap = C::encode_subject('3年2班', '數學', [
    'handle' => 'swap', 'swap_date' => '2026-07-15', 'swap_period' => '第3節', 'swap_subject' => '國語',
]);
$d = C::decode_subject($swap);
assert($d['handle'] === 'swap' && $d['swap_date'] === '2026-07-15' && $d['swap_period'] === '第3節' && $d['swap_subject'] === '國語');
assert(C::handle_text($d['handle']) === '與他人調課');

//補課列：無對調科目
$makeup = C::decode_subject(C::encode_subject('', '數學', ['handle' => 'makeup', 'swap_date' => '2026-07-16', 'swap_period' => '第5節']));
assert($makeup['handle'] === 'makeup' && $makeup['swap_subject'] === '');

//日薪列（班級科目皆空且為代課）仍存空字串
assert(C::encode_subject('', '', ['handle' => 'substitute']) === '');

echo "OK\n";
