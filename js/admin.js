$(document).ready(function () {
  $("#switch-table").DataTable();

  const lastCheck = new Date().toLocaleString();
  $("#last-check").text(`Last Check: ${lastCheck}`);

  $(".view-details").click(function () {
    const switchId = $(this).data("id");
    Swal.fire({
      title: "Switch Details",
      text: `Details for Switch ID: ${switchId}`,
      icon: "info",
    });
  });
});
