'use strict';

// DataTable initialization
$(function () {
    var dt_security_logs = $('#security-logs-table');
    
    if (dt_security_logs.length) {
        dt_security_logs.DataTable({
            dom: '<"row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6 d-flex justify-content-center justify-content-md-end"f>>t<"row"<"col-sm-12 col-md-6"i><"col-sm-12 col-md-6"p>>',
            language: {
                search: 'بحث:',
                lengthMenu: 'عرض _MENU_ سجلات',
                info: 'عرض _START_ إلى _END_ من _TOTAL_ سجل',
                paginate: {
                    first: 'الأول',
                    previous: 'السابق',
                    next: 'التالي',
                    last: 'الأخير'
                },
                emptyTable: 'لا توجد بيانات متاحة في الجدول',
                zeroRecords: 'لم يتم العثور على سجلات مطابقة',
                infoEmpty: 'عرض 0 إلى 0 من 0 سجل',
                infoFiltered: '(تمت التصفية من _MAX_ سجل)'
            },
            order: [[0, 'desc']], // ترتيب حسب التاريخ تنازلياً
            pageLength: 10,
            responsive: true
        });
    }
});
