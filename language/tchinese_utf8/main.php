<?php

xoops_loadLanguage('main', 'tadtools');
define('_MD_JILLLEAVE_INDEX', '教師請假主頁');
define('_MD_JILLLEAVE_IS_ADVISOR_DEF', '1=級任');
define('_MD_JILLLEAVE_STATUS_DEF', '1=已通過');
define('_MD_JILLLEAVE_SN', '請假編號');
define('_MD_JILLLEAVE_UID', '請假者編號');
define('_MD_JILLLEAVE_LEAVERS', '請假者姓名');
define('_MD_JILLLEAVE_CATE_SN', '假別編號');
define('_MD_JILLLEAVE_IS_ADVISOR', '級科任');
define('_MD_JILLLEAVE_GRADE_CLASS', '班級');
define('_MD_JILLLEAVE_START_DATE', '起始日期');
define('_MD_JILLLEAVE_END_DATE', '結束日期');
define('_MD_JILLLEAVE_STATUS', '審核狀態');
define('_MD_JILLLEAVE_CREATE_DATE', '申請時間');
define('_MD_JILLLEAVE_UPDATE_DATE', '最後更新時間');
define('_MD_JILLLEAVE_CLASS_CLASS_SN', '節次編號');
define('_MD_JILLLEAVE_CLASS_SUBSTITUTE_SN', '關聯代課編號');
define('_MD_JILLLEAVE_CLASS_SN', '關聯請假編號');
define('_MD_JILLLEAVE_CLASS_CLASS_PERIOD', '節次');
define('_MD_JILLLEAVE_CLASS_SUBJECT', '科目');
define('_MD_JILLLEAVE_CLASS_SUBSTITUTE_TEACHER', '代課老師');
define('_MD_JILLLEAVE_CATE', '假別分類');
define('_MD_JILLLEAVE_CATE_ENABLE_DEF', '1=啟用');
define('_MD_JILLLEAVE_CATE_CATE_SN', '假別編號');
define('_MD_JILLLEAVE_CATE_CATE_TITLE', '假別名稱');
define('_MD_JILLLEAVE_CATE_CATE_SORT', '假別排序');
define('_MD_JILLLEAVE_CATE_ENABLE', '狀態');
define('_MD_JILLLEAVE_CATE_FORCE_PAY', '鎖定支付方式');
define('_MD_JILLLEAVE_CATE_FORCE_PAY_NONE', '不鎖定');
define('_MD_JILLLEAVE_SUBSTITUTE', '代課管理');
define('_MD_JILLLEAVE_SUBSTITUTE_PAY_DEF', '自費');
define('_MD_JILLLEAVE_SUBSTITUTE_TYPE_DEF', '日薪');
define('_MD_JILLLEAVE_SUBSTITUTE_SUBSTITUTE_SN', '代課編號');
define('_MD_JILLLEAVE_SUBSTITUTE_SN', '關聯請假編號');
define('_MD_JILLLEAVE_SUBSTITUTE_SUBSTITUTE_DATE', '代課日期');
define('_MD_JILLLEAVE_SUBSTITUTE_PAY', '支付方式');
define('_MD_JILLLEAVE_SUBSTITUTE_TYPE', '調代課類型');
define('_MD_JILLLEAVE_ADM', '設定管理員');

//顯示用文字
define('_MD_JILLLEAVE_ADVISOR', '級任');
define('_MD_JILLLEAVE_SUBJECT_TEACHER', '科任');
define('_MD_JILLLEAVE_STATUS_0', '待審核');
define('_MD_JILLLEAVE_STATUS_1', '已通過');
define('_MD_JILLLEAVE_STATUS_2', '駁回');
define('_MD_JILLLEAVE_PAY_SELF', '自費');
define('_MD_JILLLEAVE_PAY_SCHOOL', '公費');
define('_MD_JILLLEAVE_PAY_NONE', '無');
define('_MD_JILLLEAVE_TYPE_DAILY', '日薪');
define('_MD_JILLLEAVE_TYPE_HOUR', '鐘點');
define('_MD_JILLLEAVE_TYPE_SWAP', '補調課');
define('_MD_JILLLEAVE_APPLY_SWAP', '申請補調課');

//代課批次表單
define('_MD_JILLLEAVE_SUBSTITUTE_INFO', '代課資訊');
define('_MD_JILLLEAVE_ADD_ROW', '新增一列');

//代課表單（依起訖日期動態生成，js/leave_form.js 使用）
define('_MD_JILLLEAVE_PAY_SELF_FULL', '自費代課');
define('_MD_JILLLEAVE_PAY_SCHOOL_FULL', '公費派代');
define('_MD_JILLLEAVE_TEACHER_ASSIGN', '教學組派代');
define('_MD_JILLLEAVE_TEACHER_INPUT', '自行覓代');
define('_MD_JILLLEAVE_ALLDAY', '全日');
define('_MD_JILLLEAVE_PERIOD_LABEL', '第%s節');
define('_MD_JILLLEAVE_WEEKDAYS', '日,一,二,三,四,五,六');
define('_MD_JILLLEAVE_MSG_DATE_ORDER', '起始日期不能大於結束日期');
define('_MD_JILLLEAVE_MSG_NO_PERIOD', '尚未勾選代課節次');
define('_MD_JILLLEAVE_MSG_NO_SUBJECT', '請輸入科目');
define('_MD_JILLLEAVE_MSG_NO_TEACHER', '請輸入代課老師');
define('_MD_JILLLEAVE_MSG_NO_GRADE_CLASS', '請選擇代課班級');
define('_MD_JILLLEAVE_CHECK_PERIOD_TIP', '請勾選左側核取方塊以啟用該節次填寫欄位');

//遺課處理方式（委託代課／自己補課／與他人調課）
define('_MD_JILLLEAVE_HANDLE', '處理方式');
define('_MD_JILLLEAVE_HANDLE_SUBSTITUTE', '委託代課');
define('_MD_JILLLEAVE_HANDLE_MAKEUP', '自己補課');
define('_MD_JILLLEAVE_HANDLE_SWAP', '與他人調課');
define('_MD_JILLLEAVE_CHANGED', '異動後');
define('_MD_JILLLEAVE_CHANGED_DATE', '異動後日期');
define('_MD_JILLLEAVE_CHANGED_PERIOD', '異動後節次');
define('_MD_JILLLEAVE_SWAP_SUBJECT', '對調科目');
define('_MD_JILLLEAVE_SWAP_TEACHER', '對調教師／班級');
define('_MD_JILLLEAVE_MSG_NO_CHANGED', '請選擇異動後日期與節次');
define('_MD_JILLLEAVE_MSG_NO_SWAP_TEACHER', '請輸入對調教師');
define('_MD_JILLLEAVE_MSG_NO_SWAP_SUBJECT', '請輸入對調科目');
define('_MD_JILLLEAVE_MSG_CONFLICT_PERIOD', '{date} 第 {period} 節已重複（衝堂），請重新選擇異動後節次');

//補調課異動後節次與鐘點請假衝突告警
define('_MD_JILLLEAVE_MSG_HOUR_CONFLICT', '異動後節次與鐘點請假衝突：{date} {period}（假單編號 {sn}），該節已安排鐘點代課，請調整異動後節次');
define('_MD_JILLLEAVE_HOUR_CONFLICT_SAVE_FAIL', '儲存失敗：異動後節次與鐘點請假節次衝突，請修正後重新送出');

//核章欄（僅列印用，不寫入資料庫）
define('_MD_JILLLEAVE_SIGN_TEACHER', '請假教師');
define('_MD_JILLLEAVE_SIGN_GROUP_LEADER', '教學組長');
define('_MD_JILLLEAVE_SIGN_DIRECTOR', '教務主任');
define('_MD_JILLLEAVE_SIGN_TITLE', '核章');

//科任逐節班級
define('_MD_JILLLEAVE_GRADE_SELECT', '年級');
define('_MD_JILLLEAVE_CLASS_INPUT', '班');

//同第一天功能
define('_MD_JILLLEAVE_COPY_FIRST_DAY', '同第一天');
define('_MD_JILLLEAVE_COPY_FIRST_DAY_TIP', '複製第一天的科目與代課老師（班級與節次請自行調整）');

//代課總覽
define('_MD_JILLLEAVE_MONTH', '月份');
define('_MD_JILLLEAVE_FILTER', '篩選');
define('_MD_JILLLEAVE_EXPORT_EXCEL', '匯出鐘點費清冊');
define('_MD_JILLLEAVE_EXPORT_TITLE', '鐘點費清冊 ');

//管理人員設定
define('_MD_JILLLEAVE_ADM_EMAIL', '管理人員 Email');
define('_MD_JILLLEAVE_ADM_EMAIL_DESC', '多筆 Email 請以「;」分號隔開，列於此處的使用者登入後即具有本模組的管理權限（需重新登入生效）。');

//狀態
define('_MD_JILLLEAVE_ENABLED', '已啟用');
define('_MD_JILLLEAVE_DISABLED', '已停用');

//訊息
define('_MD_JILLLEAVE_DUPLICATE_LEAVE', '您在該日期區間已有假單，無法重複請假');
define('_MD_JILLLEAVE_TOKEN_ERROR', '安全驗證失敗，請重新整理頁面後再試');
define('_MD_JILLLEAVE_APPROVED_LOCKED', '假單已通過審核，僅管理員可修改或刪除');
define('_MD_JILLLEAVE_NO_PERMISSION', '無操作權限');
define('_MD_JILLLEAVE_NO_CONDITION', '無查詢條件');
define('_MD_JILLLEAVE_SUBSTITUTE_SAVE_FAIL', '代課資料儲存失敗，請假資料未寫入，請重新操作');

//模組設定
define('_MD_JILLLEAVE_CONFIG', '系統參數');

