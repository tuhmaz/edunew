$(function() {
  const selectAllCheckbox = $('#select-all-logs');
  const logCheckboxes = $('.log-checkbox');
  const bulkDeleteBtn = $('#bulk-delete-btn');
  const toggleSelectAllBtn = $('#toggle-select-all-btn');

  let allSelected = false;

  console.log("Loaded: selectAllCheckbox:", selectAllCheckbox.length);
  console.log("Loaded: logCheckboxes:", logCheckboxes.length);

  // تحديد الكل وإلغاء التحديد
  toggleSelectAllBtn.on('click', function() {
      allSelected = !allSelected;
      console.log("Toggle Select All:", allSelected);

      logCheckboxes.prop('checked', allSelected);
      selectAllCheckbox.prop('checked', allSelected);
      updateBulkDeleteButton();
      updateToggleButtonText();
  });

  selectAllCheckbox.on('change', function() {
      console.log("Select All Checkbox Changed:", $(this).is(':checked'));

      logCheckboxes.prop('checked', $(this).is(':checked'));
      updateBulkDeleteButton();
  });

  logCheckboxes.on('change', function() {
      const allChecked = logCheckboxes.length === logCheckboxes.filter(':checked').length;
      console.log("Individual Checkbox Changed, All Checked:", allChecked);

      selectAllCheckbox.prop('checked', allChecked);
      updateBulkDeleteButton();
  });

  function updateBulkDeleteButton() {
      const anyChecked = logCheckboxes.filter(':checked').length > 0;
      console.log("Bulk Delete Button Visibility:", anyChecked);

      bulkDeleteBtn.toggleClass('d-none', !anyChecked);
  }

  function updateToggleButtonText() {
      toggleSelectAllBtn.html(allSelected ?
          '<i class="ri-checkbox-multiple-blank-line me-1"></i> إلغاء تحديد الكل' :
          '<i class="ri-checkbox-multiple-line me-1"></i> تحديد الكل'
      );
  }

  window.submitBulkDelete = function() {
      if (confirm('هل أنت متأكد من حذف السجلات المحددة؟')) {
          $('#bulk-delete-form').submit();
      }
  };

  // تحديث حالة السجل
  $('.resolve-form').on('submit', function(e) {
      e.preventDefault();
      const form = $(this);
      
      $.ajax({
          url: form.attr('action'),
          method: 'POST',
          data: form.serialize(),
          success: function(response) {
              // تحديث واجهة المستخدم
              const statusCell = form.closest('td');
              statusCell.html('<span class="badge bg-label-success">تم الحل</span>');
              
              // إظهار رسالة نجاح
              if (response.message) {
                  // يمكنك إضافة كود لعرض رسالة النجاح هنا
              }
          },
          error: function(xhr) {
              console.error('Error:', xhr);
              alert('حدث خطأ أثناء تحديث حالة السجل');
          }
      });
  });
});
