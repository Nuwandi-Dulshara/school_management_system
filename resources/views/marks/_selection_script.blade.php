<script>
    const marksClass = document.getElementById('class_id');
    const marksSection = document.getElementById('section_id');
    const marksExam = document.getElementById('exam_id');

    const filterMarksSections = () => {
        [...marksSection.options].forEach((option, index) => {
            option.hidden = index > 0 && marksClass.value && option.dataset.classId !== marksClass.value;
        });
    };

    marksClass?.addEventListener('change', () => {
        marksSection.value = '';
        filterMarksSections();
    });
    marksExam?.addEventListener('change', () => {
        const option = marksExam.selectedOptions[0];
        if (option?.dataset.classId) {
            marksClass.value = option.dataset.classId;
            filterMarksSections();
            marksSection.value = option.dataset.sectionId;
        }
    });
    filterMarksSections();
</script>
