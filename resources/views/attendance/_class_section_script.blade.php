<script>
    const attendanceClassSelect = document.getElementById(@json($classSelectId ?? 'filter_class_id'));
    const attendanceSectionSelect = document.getElementById(@json($sectionSelectId ?? 'filter_section_id'));

    function filterAttendanceSections() {
        const selectedClass = attendanceClassSelect?.value;
        if (!attendanceSectionSelect) return;

        Array.from(attendanceSectionSelect.options).forEach((option) => {
            if (!option.value) return;
            const visible = !selectedClass || option.dataset.classId === selectedClass;
            option.hidden = !visible;
            option.disabled = !visible;
        });

        if (attendanceSectionSelect.selectedOptions[0]?.disabled) {
            attendanceSectionSelect.value = '';
        }
    }

    attendanceClassSelect?.addEventListener('change', filterAttendanceSections);
    filterAttendanceSections();
</script>
