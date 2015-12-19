bootbox.dialog({
    message: "The publication has been added.",
    title: "Success.",
    buttons: {
        success: {
            label: "View it",
            className: "btn-success",
            callback: function() {
                window.location.replace("{{ base }}/content/{{ id }}")
            }
        },
        main: {
            label: "OK",
            className: "btn-primary",
            callback: function() {
            }
        }
    }
});