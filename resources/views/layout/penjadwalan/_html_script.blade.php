<?php

use Illuminate\Support\Facades\Request;
?>
<!-- Main Script -->
<script type="text/javascript">
    gantt.setWorkTime({
        hours: [8, 16] // Working hours from 8:00 AM to 4:00 PM
    });

    // Configure the Gantt chart scale
    // gantt.config.scale_unit = "day";
    // gantt.config.date_scale = "%d %M";
    gantt.config.scales = [{
        unit: "minute",
        step: 10,
        format: "%H:%i",
        css: function(date) {
            if (!gantt.isWorkTime({
                    date: date,
                    unit: "day"
                })) {
                return "weekend"
            }
        }
    }];
    // gantt.config.subscales = [{
    //         unit: "hour",
    //         step: 1,
    //         date: "%H:%i"
    //     } // Display hours
    // ];

    // Show only working hours in the timeline
    gantt.config.work_time = true;
    gantt.config.skip_off_time = true;
    gantt.config.duration_unit = "hour";
    gantt.config.columns = [{
            name: "text",
            label: "Room Name",
            width: "*",
            tree: true
        },
        {
            name: "add",
            label: "",
            width: 44
        } // Add button stays visible
    ];
    gantt.config.date_format = "%Y-%m-%d %H:%i";
    gantt.templates.progress_text = function() {
        return ""; // Removes text on progress bars
    };
    gantt.config.show_progress = false; // Disable progress bar
    gantt.config.drag_links = false; // Disable creating links
    gantt.templates.link_class = function() {
        return "hide-link"; // Hides links
    };

    var startDate = new Date("2024-12-18 08:00:00");
    var endDate = new Date("2024-12-18 16:00:00");

    gantt.config.start_date = startDate;
    gantt.config.end_date = endDate;

    gantt.init("gantt_here");
    gantt.parse({
        data: [{
                id: 1,
                text: "Room #1",
                start_date: "2024-12-18 08:00",
                duration: 8,
                parent: 0,
                open: true
            },
            {
                id: 2,
                text: "Course 1",
                start_date: "2024-12-18 08:00",
                duration: 1,
                parent: 1
            },
            {
                id: 3,
                text: "Course 2",
                start_date: "2024-12-18 09:00",
                duration: 1,
                parent: 1
            },
            {
                id: 4,
                text: "Room #2",
                start_date: "2024-12-18 08:00",
                duration: 8,
                parent: 0
            }
        ]
    });

    gantt.eachTask(function(task) {
        if (task.parent === 0) {
            $('.gantt_bar_task[task_id="' + task.id + '"]').css('display', 'none');
        } else {
            $('.gantt_row[task_id="' + task.id + '"] .gantt_cell[data-column-name="add"] .gantt_add').remove();

        }
    });
    $(".gantt_grid_head_cell[column_id='add']").remove();
    $(".gantt_grid_head_cell[column_id='text']").css('width', '100%');
</script>