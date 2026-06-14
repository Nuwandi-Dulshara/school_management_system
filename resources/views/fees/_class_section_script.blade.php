<script>
    const feeClass = document.getElementById('school_class_id');
    const feeSection = document.getElementById('school_section_id');
    const feeStudent = document.getElementById('student_id');
    const updateFeeOptions = () => {
        [...feeSection.options].forEach((option, index) => option.hidden = index > 0 && feeClass.value && option.dataset.classId !== feeClass.value);
        [...feeStudent.options].forEach((option, index) => option.hidden = index > 0 && (
            feeClass.value && option.dataset.classId !== feeClass.value ||
            feeSection.value && option.dataset.sectionId !== feeSection.value
        ));
    };
    feeClass?.addEventListener('change', () => { feeSection.value = ''; feeStudent.value = ''; updateFeeOptions(); });
    feeSection?.addEventListener('change', () => { feeStudent.value = ''; updateFeeOptions(); });
    updateFeeOptions();
</script>
