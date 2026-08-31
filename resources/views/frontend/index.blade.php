<!DOCTYPE html>
<html lang="en">

<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title> {{ $setting->meta_title }}</title>
<!-- SEO Meta Tags -->
<meta name="description" content="{{ $setting->meta_description }}">
<meta name="keywords" content="{{ $setting->meta_keywords }}">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.0/css/all.min.css"
integrity="sha512-10/jx2EXwxxWqCLX/hHth/vu2KY3jCF70dCQB8TSgNjbCVAC/8vai53GfMDrO2Emgwccf2pJqxct9ehpzG+MTw=="
crossorigin="anonymous" referrerpolicy="no-referrer" />
<link rel="stylesheet" href="{{ asset('assets/css/style.css') }}">
@if ($setting->header)
{!! $setting->header !!}
@endif
</head>

<body>
<header class="header-section">
<nav class="navbar navbar-expand-lg navbar-light">
<div class="container">
<a class="navbar-brand" href="#">
<!-- TODO: reemplazar marca/ícono por el de CIEN -->
<i class="bi bi-lungs"></i> Clínica PulmoLab
</a>
<button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
<span class="navbar-toggler-icon"></span>
</button>
<div class="collapse navbar-collapse" id="navbarNav">
<ul class="navbar-nav ms-auto">
@guest
<li class="nav-item">
<a class="nav-link active" href="{{ route('login') }}">Ingresar</a>
</li>
<li class="nav-item">
<a class="nav-link" href="{{ route('register') }}">Registrarse</a>
</li>
@endguest

@auth
<li class="nav-item">
<a class="nav-link" href="{{ route('dashboard') }}">Dashboard</a>
</li>
@endauth

</ul>
</div>
</div>
</nav>
</header>

<div class="container">
<div class="booking-container">
<div class="booking-header">
<h2><i class="bi bi-calendar-check"></i> Reserva de consultas</h2>
<!-- TODO: reemplazar dirección por la de CIEN -->
<p class="mb-0">Dir. Brandsen 157, Ciudad de Formosa</p>
</div>

<div class="booking-steps position-relative">
<div class="step active" data-step="1">
<div class="step-number">1</div>
<div class="step-title">Profesional</div>
</div>
<div class="step" data-step="2">
<div class="step-number">2</div>
<div class="step-title">Categoría</div>
</div>
<div class="step" data-step="3">
<div class="step-number">3</div>
<div class="step-title">Servicio</div>
</div>
<div class="step" data-step="4">
<div class="step-number">4</div>
<div class="step-title">Fecha y Hora</div>
</div>
<div class="step" data-step="5">
<div class="step-number">5</div>
<div class="step-title">Confirmar</div>
</div>
<div class="progress-bar-steps">
<div class="progress"></div>
</div>
</div>

<div class="booking-content">
<!-- Step 1: Professional Selection -->
<div class="booking-step active" id="step1">
<h3 class="mb-4">Selección</h3>
<div class="row row-cols-1 row-cols-md-3 g-4" id="employees-container">
<!-- Employees will be inserted here by jQuery -->
</div>
</div>

<!-- Step 2: Category Selection -->
<div class="booking-step" id="step2">
<h3 class="mb-4">Selección</h3>
<div class="selected-employee-name mb-3 fw-bold"></div>
<div class="row row-cols-1 row-cols-md-3 g-4" id="categories-container">
<!-- Categories will be loaded dynamically based on the selected professional -->
</div>
</div>

<!-- Step 3: Service Selection -->
<div class="booking-step" id="step3">
<h3 class="mb-4">Selección</h3>
<div class="selected-category-name mb-3 fw-bold"></div>
<div class="row row-cols-1 row-cols-md-3 g-4" id="services-container">
<!-- Services will be loaded dynamically based on category -->
</div>
</div>

<!-- Step 4: Date and Time Selection -->
<div class="booking-step" id="step4">
<h3 class="mb-4">Selección</h3>
<div class="selected-service-name mb-3 fw-bold"></div>

<div class="row">
<div class="col-md-6">
<div class="card mb-4">
<div class="card-header d-flex justify-content-between align-items-center">
<button class="btn btn-sm btn-outline-secondary" id="prev-month"><i
class="bi bi-chevron-left"></i></button>
<h5 class="mb-0" id="current-month">2025</h5>
<button class="btn btn-sm btn-outline-secondary" id="next-month"><i
class="bi bi-chevron-right"></i></button>
</div>
<div class="card-body">
<table class="table table-calendar">
<thead>
<tr>
<th>Dom</th>
<th>Lun</th>
<th>Mar</th>
<th>Mié</th>
<th>Jue</th>
<th>Vie</th>
<th>Sáb</th>
</tr>
</thead>
<tbody id="calendar-body">
<!-- Calendar will be generated dynamically -->
</tbody>
</table>
</div>
</div>
</div>
<div class="col-md-6">
<div class="card">
<div class="card-header">
<h5 class="mb-0">Horarios disponibles</h5>
<div id="selected-date-display" class="text-muted small"></div>
</div>
<div class="card-body">
<div id="time-slots-container" class="d-flex flex-wrap">
<!-- Time slots will be loaded dynamically -->
<div class="text-center text-muted w-100 py-4">
Seleccione una fecha para ver los horarios
</div>
</div>
</div>
</div>
</div>
</div>
</div>

<!-- Step 5: Confirmation -->
<div class="booking-step" id="step5">
<h3 class="mb-4">Confirmar</h3>
<div class="card">
<div class="card-header bg-light">
<h5 class="mb-0">Resúmen</h5>
</div>
<div class="card-body">
<div class="summary-item">
<div class="row">
<div class="col-md-4 text-muted">Profesional:</div>
<div class="col-md-8" id="summary-employee"></div>
</div>
</div>
<div class="summary-item">
<div class="row">
<div class="col-md-4 text-muted">Categoría:</div>
<div class="col-md-8" id="summary-category"></div>
</div>
</div>
<div class="summary-item">
<div class="row">
<div class="col-md-4 text-muted">Servicio:</div>
<div class="col-md-8" id="summary-service"></div>
</div>
</div>
<div class="summary-item">
<div class="row">
<div class="col-md-4 text-muted">Fecha y hora:</div>
<div class="col-md-8" id="summary-datetime"></div>
</div>
</div>
<div class="summary-item">
<div class="row">
<div class="col-md-4 text-muted">Duración:</div>
<div class="col-md-8" id="summary-duration"></div>
</div>
</div>
<div class="summary-item">
<div class="row">
<div class="col-md-4 text-muted">Precio:</div>
<div class="col-md-8" id="summary-price"></div>
</div>
</div>

<div class="mt-4">
<h5>Información</h5>
<form id="customer-info-form">
@csrf
<div class="row g-3">
<div class="col-md-6">
<label for="customer-name" class="form-label">Nombre</label>
<input type="text" class="form-control" id="customer-name" required>
</div>
<div class="col-md-6">
<label for="customer-email" class="form-label">Email</label>
<input type="email" class="form-control" id="customer-email" required>
</div>
<div class="col-md-12">
<label for="customer-phone" class="form-label">Teléfono</label>
<input type="tel" class="form-control" id="customer-phone" required>
</div>

<div class="col-md-6">
<label for="customer-obra-social" class="form-label">Obra social</label>
<select class="form-control" id="customer-obra-social">
<option value="">-- Sin obra social --</option>
@foreach ($obrasSociales as $obra)
<option value="{{ $obra->nombre }}">{{ $obra->nombre }}</option>
@endforeach
</select>
</div>

<div class="col-12">
<label for="customer-notes" class="form-label">Datos relevantes (Opcional)</label>
<textarea class="form-control" id="customer-notes" rows="3"></textarea>
</div>
</div>
</form>
</div>
</div>
</div>
</div>
</div>

<div class="booking-footer">
<button class="btn btn-outline-secondary" id="prev-step" disabled>
<i class="bi bi-arrow-left"></i> Volver
</button>
<button class="btn btn-primary" id="next-step">
Avanzar <i class="bi bi-arrow-right"></i>
</button>
</div>
</div>
</div>

<footer>
<div class="container pb-2">
<div class="row text-center">
<!-- TODO: reemplazar nombre de clínica en el pie de página -->
<span><span>© {{ date('Y') }} Clínica PulmoLab. Todos los derechos reservados. Desarrollado por <a href="https://vfixtechnology.com" target="_blank" rel="noopener" style="color:#3490dc;font-weight:bold;">VFIX Technology</a>.</span></span>
</div>
</div>
</footer>

<!-- Success Modal -->
<div class="modal fade" id="bookingSuccessModal" tabindex="-1" aria-hidden="true">
<div class="modal-dialog modal-dialog-centered">
<div class="modal-content">
<div class="modal-header bg-success text-white">
<h5 class="modal-title">Cita agendada</h5>
<button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
aria-label="Close"></button>
</div>
<div class="modal-body text-center p-4">
<i class="bi bi-check-circle text-success" style="font-size: 4rem;"></i>
<h4 class="mt-3">Gracias</h4>
<p>Tu cita ha sido agendada</p>
<div class="alert alert-info mt-3">
<p class="mb-0">Se ha enviado un correo para confirmar</p>
</div>
<div class="booking-details mt-4 text-start">
<h5>Detalles:</h5>
<div id="modal-booking-details"></div>
</div>
</div>
<div class="modal-footer">
<button type="button" class="btn btn-primary" data-bs-dismiss="modal">Cerrar</button>
</div>
</div>
</div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
$(document).ready(function() {

    // Profesionales precargados desde el controlador (paso 1)
    const employees = @json($employees);

    const employeesContainer = $('#employees-container');

    let employeesHtml = '';
    $.each(employees, function(index, employee) {
        employeesHtml += `
        <div class="col">
        <div class="card border h-100 employee-card text-center rounded p-2" data-employee="${employee.id}">
        <div class="card-body">
        <h5 class="card-title">${employee.user ? employee.user.name : ''}</h5>
        ${employee.bio ? `<p class="card-text text-muted small">${employee.bio}</p>` : ''}
        </div>
        </div>
        </div>
        `;
    });

    employeesContainer.html(employeesHtml); // Insert all generated HTML at once


    // Booking state
    let bookingState = {
        currentStep: 1,
        selectedEmployee: null,
        selectedCategory: null,
        selectedService: null,
        selectedDate: null,
        selectedTime: null
    };

    // Initialize the booking system
    updateProgressBar();
    generateCalendar();

    // Step navigation
    $("#next-step").click(function() {
        const currentStep = bookingState.currentStep;

        // Validate current step before proceeding
        if (!validateStep(currentStep)) {
            return;
        }

        if (currentStep < 5) {
            goToStep(currentStep + 1);
        } else {
            // Submit booking
            if ($("#customer-info-form")[0].checkValidity()) {
                submitBooking();
            } else {
                $("#customer-info-form")[0].reportValidity();
            }
        }
    });

    $("#prev-step").click(function() {
        if (bookingState.currentStep > 1) {
            goToStep(bookingState.currentStep - 1);
        }
    });

    // Professional selection
    $(document).on("click", ".employee-card", function() {
        $(".employee-card").removeClass("selected");
        $(this).addClass("selected");

        const employeeId = $(this).data("employee");
        const employee = employees.find(e => e.id === employeeId);

        bookingState.selectedEmployee = employee;

        // Reset subsequent selections
        bookingState.selectedCategory = null;
        bookingState.selectedService = null;
        bookingState.selectedDate = null;
        bookingState.selectedTime = null;

        // Clear previous selections UI
        $(".category-card, .service-card, .calendar-day, .time-slot").removeClass("selected");

        // Show loading state for categories
        $("#categories-container").html(
            '<div class="col-12 text-center py-5"><div class="spinner-border text-primary" role="status"></div></div>'
        );

        // Update the category step with categories available for this professional
        updateCategoriesStep(employeeId);
    });

    // Category selection
    $(document).on("click", ".category-card", function() {
        $(".category-card").removeClass("selected");
        $(this).addClass("selected");

        const categoryId = $(this).data("category");
        const categoryTitle = $(this).find('.card-title').text();

        bookingState.selectedCategory = {
            id: categoryId,
            title: categoryTitle
        };

        // Reset subsequent selections
        bookingState.selectedService = null;
        bookingState.selectedDate = null;
        bookingState.selectedTime = null;

        // Clear previous selections UI
        $(".service-card, .calendar-day, .time-slot").removeClass("selected");

        // Show loading state for services
        $("#services-container").html(
            '<div class="text-center py-5"><div class="spinner-border text-primary" role="status"></div></div>'
        );

        // Update the service step with services for this professional + category
        updateServicesStep(bookingState.selectedEmployee.id, categoryId);
    });

    // Service selection
    $(document).on("click", ".service-card", function() {
        $(".service-card").removeClass("selected");
        $(this).addClass("selected");

        const serviceId = $(this).data("service");
        const serviceTitle = $(this).find('.card-title').text();
        const servicePrice = $(this).find('.fw-bold').text();

        // Store the selected service in booking state
        bookingState.selectedService = {
            id: serviceId,
            title: serviceTitle,
            price: servicePrice
        };

        // Reset subsequent selections
        bookingState.selectedDate = null;
        bookingState.selectedTime = null;

        // Clear previous selections UI
        $(".calendar-day, .time-slot").removeClass("selected");
    });


    // Date selection
    $(document).on("click", ".calendar-day:not(.disabled)", function() {
        $(".calendar-day").removeClass("selected");
        $(this).addClass("selected");

        const date = $(this).data("date");
        bookingState.selectedDate = date;

        // Reset time selection
        bookingState.selectedTime = null;

        // Update time slots based on employee availability
        updateTimeSlots(date);
    });

    // Time slot selection
    $(document).on("click", ".time-slot:not(.disabled)", function() {
        $(".time-slot").removeClass("selected");
        $(this).addClass("selected");

        const time = $(this).data("time");
        bookingState.selectedTime = time;
    });

    // Calendar navigation
    $("#prev-month").click(function() {
        navigateMonth(-1);
    });

    $("#next-month").click(function() {
        navigateMonth(1);
    });

    // Functions
    function goToStep(step) {
        // Hide all steps
        $(".booking-step").removeClass("active");

        // Show the target step
        $(`#step${step}`).addClass("active");

        // Update the step indicators
        $(".step").removeClass("active completed");

        for (let i = 1; i <= 5; i++) {
            if (i < step) {
                $(`.step[data-step="${i}"]`).addClass("completed");
            } else if (i === step) {
                $(`.step[data-step="${i}"]`).addClass("active");
            }
        }

        // Update the current step
        bookingState.currentStep = step;

        // Update the navigation buttons
        updateNavigationButtons();

        // Update the progress bar
        updateProgressBar();

        // If we're entering the date/time step, (re)initialize the calendar for the chosen service
        if (step === 4) {
            updateCalendar();
        }

        // If we're on the confirmation step, update the summary
        if (step === 5) {
            updateSummary();
        }

        // Scroll to top of booking container
        $(".booking-container")[0].scrollIntoView({
            behavior: "smooth"
        });
    }


    function updateProgressBar() {
        const progress = ((bookingState.currentStep - 1) / 4) * 100;
        $(".progress-bar-steps .progress").css("width", `${progress}%`);
    }


    function updateNavigationButtons() {
        // Enable/disable previous button
        if (bookingState.currentStep === 1) {
            $("#prev-step").prop("disabled", true);
        } else {
            $("#prev-step").prop("disabled", false);
        }

        // Update next button text
        if (bookingState.currentStep === 5) {
            $("#next-step").html('Confirmar turno <i class="bi bi-check-circle"></i>');
        } else {
            $("#next-step").html('Avanzar <i class="bi bi-arrow-right"></i>');
        }
    }


    function validateStep(step) {
        switch (step) {
            case 1:
                if (!bookingState.selectedEmployee) {
                    alert("Por favor selecciona un profesional");
                    return false;
                }
                return true;
            case 2:
                if (!bookingState.selectedCategory) {
                    alert("Por favor selecciona una categoria");
                    return false;
                }
                return true;
            case 3:
                if (!bookingState.selectedService) {
                    alert("Por favor selecciona un servicio");
                    return false;
                }
                return true;
            case 4:
                if (!bookingState.selectedDate) {
                    alert("Por favor selecciona una fecha");
                    return false;
                }
                if (!bookingState.selectedTime) {
                    alert("Por favor selecciona una franja horaria");
                    return false;
                }
                return true;
            default:
                return true;
        }
    }


    function updateCategoriesStep(employeeId) {
        // Make AJAX request to get categories available for this professional
        $.ajax({
            url: "{{ url('employees') }}/" + employeeId + "/categories",
               type: 'GET',
               dataType: 'json',
               success: function(response) {
                   // Update professional name display
                   $(".selected-employee-name").text(
                       `Profesional: ${bookingState.selectedEmployee.user.name}`);

                   if (response.success && response.categories) {
                       const categories = response.categories;

                       // Clear categories container
                       $("#categories-container").empty();

                       // Add categories with animation delay
                       categories.forEach((category, index) => {
                           const categoryCard = `
                           <div class="col animate-slide-in" style="animation-delay: ${index * 100}ms">
                           <div class="card border h-100 category-card text-center rounded p-2" data-category="${category.id}">
                           <div class="card-body">
                           ${category.image ? `<img class="img-fluid w-57 mb-2 rounded" src="uploads/images/category/${category.image}">` : ""}
                           <h5 class="card-title">${category.title}</h5>
                           <p class="card-text">${category.body}</p>
                           </div>
                           </div>
                           </div>
                           `;
                           $("#categories-container").append(categoryCard);
                       });
                   } else {
                       $("#categories-container").html(
                           '<div class="col-12 text-center py-5"><p>No categories available for this professional.</p></div>'
                       );
                   }
               },
               error: function(xhr) {
                   console.error(xhr);
                   $("#categories-container").html(
                       '<div class="col-12 text-center py-5"><p>Error loading categories. Please try again.</p></div>'
                   );
               }
        });
    }


    function updateServicesStep(employeeId, categoryId) {
        // Make AJAX request to get services for this professional + category
        $.ajax({
            url: "{{ url('employees') }}/" + employeeId + "/categories/" + categoryId + "/services",
               type: 'GET',
               dataType: 'json',
               success: function(response) {
                   if (response.success && response.services) {
                       const services = response.services;

                       // Update category name display
                       $(".selected-category-name").text(
                           `Categoría: ${services[0]?.category?.title || bookingState.selectedCategory.title}`);

                       // Clear services container
                       $("#services-container").empty();

                       // Add services with animation delay
                       services.forEach((service, index) => {
                           // Determine the price display
                           let priceDisplay;
                           if (service.sale_price) {
                               // If sale price exists, show both with strike-through on original price
                               priceDisplay =
                               `<span class="text-decoration-line-through text-muted">${service.price}</span> <span class=" fw-bold">${service.sale_price}</span>`;
                           } else {
                               // If no sale price, just show regular price normally
                               priceDisplay =
                               `<span class="fw-bold">${service.price}</span>`;
                           }

                           const serviceCard = `
                           <div class="col animate-slide-in" style="animation-delay: ${index * 100}ms">
                           <div class="card border h-100 service-card text-center p-2" data-service="${service.id}">
                           <div class="card-body">
                           ${service.image ? `<img class="img-fluid rounded mb-2" src="uploads/images/service/${service.image}">` : ""}
                           <h5 class="card-title mb-1">${service.title}</h5>
                           <p class="card-text mb-1">${service.excerpt}</p>
                           <p class="card-text">${priceDisplay}</p>
                           </div>
                           </div>
                           </div>
                           `;

                           $("#services-container").append(serviceCard);
                       });
                   } else {
                       $(".selected-category-name").text(
                           `Categoría: ${bookingState.selectedCategory.title}`);
                       $("#services-container").html(
                           '<div class="col-12 text-center py-5"><p>No services available for this category.</p></div>'
                       );
                   }
               },
               error: function(xhr) {
                   console.error(xhr);
                   $("#services-container").html(
                       '<div class="col-12 text-center py-5"><p>Error loading services. Please try again.</p></div>'
                   );
               }
        });
    }


    function generateCalendar() {
        const today = new Date();
        const currentMonth = today.getMonth();
        const currentYear = today.getFullYear();

        renderCalendar(currentMonth, currentYear);
    }

    function renderCalendar(month, year) {
        const firstDay = new Date(year, month, 1);
        const lastDay = new Date(year, month + 1, 0);
        const daysInMonth = lastDay.getDate();
        const startingDay = firstDay.getDay();

        //  Array de meses en ESPAÑOL
        const monthNames = ["Enero", "Febrero", "Marzo", "Abril", "Mayo", "Junio",
        "Julio", "Agosto", "Septiembre", "Octubre", "Noviembre", "Diciembre"];

        // Actualizar display Y guardar mes/año como data attributes
        $("#current-month")
        .text(`${monthNames[month]} ${year}`)
        .data('month', month)  // Guardar mes como número
        .data('year', year);   // Guardar año como número

        // Clear calendar
        $("#calendar-body").empty();

        // Build calendar
        let date = 1;
        for (let i = 0; i < 6; i++) {
            const row = $("<tr></tr>");

            for (let j = 0; j < 7; j++) {
                if (i === 0 && j < startingDay) {
                    row.append("<td></td>");
                } else if (date > daysInMonth) {
                    break;
                } else {
                    const today = new Date();
                    const cellDate = new Date(year, month, date);
                    const formattedDate =
                    `${year}-${(month + 1).toString().padStart(2, '0')}-${date.toString().padStart(2, '0')}`;

                    const isPast = cellDate < new Date(today.setHours(0, 0, 0, 0));

                    const cell = $(
                        `<td class="text-center calendar-day${isPast ? ' disabled' : ''}" data-date="${formattedDate}">${date}</td>`
                    );

                    row.append(cell);
                    date++;
                }
            }

            if (row.children().length > 0) {
                $("#calendar-body").append(row);
            }
        }
    }

    function navigateMonth(direction) {
        // Leer desde data attributes en lugar de parsear el texto
        let month = $("#current-month").data('month');
        let yearNum = $("#current-month").data('year');

        month += direction;

        if (month < 0) {
            month = 11;
            yearNum--;
        } else if (month > 11) {
            month = 0;
            yearNum++;
        }

        renderCalendar(month, yearNum);
    }


    function updateCalendar() {
        // Update service name display (professional and category were already chosen in steps 1-2)
        const service = bookingState.selectedService;
        $(".selected-service-name").text(`Servicio: ${service.title} (${service.price})`);

        // Clear previous selections
        bookingState.selectedDate = null;
        bookingState.selectedTime = null;
        $(".calendar-day").removeClass("selected");
        $(".time-slot").removeClass("selected");

        // Show initial state instead of loading spinner
        $("#time-slots-container").html(`
        <div class="text-center w-100 py-4">
        <div class="alert alert-info">
        <i class="bi bi-calendar-event me-2"></i>
        Seleccione una fecha para ver los horarios
        </div>
        </div>
        `);
    }

    function updateTimeSlots(selectedDate) {
        if (!selectedDate) {
            $("#time-slots-container").html(`
            <div class="text-center w-100 py-4">
            <div class="alert alert-warning">
            <i class="bi bi-exclamation-triangle me-2"></i>
            No date selected
            </div>
            </div>
            `);
            return;
        }

        const employeeId = bookingState.selectedEmployee.id;
        const apiDate = new Date(selectedDate).toISOString().split('T')[0];

        // Show loading state only when actually fetching
        $("#time-slots-container").html(`
        <div class="text-center w-100 py-4">
        <div class="spinner-border text-primary" role="status">
        <span class="visually-hidden">Loading...</span>
        </div>
        <div class="mt-2">Checking availability...</div>
        </div>
        `);

        $.ajax({
            url: "{{ url('employees') }}/" + employeeId + "/availability/" + apiDate,
               success: function(response) {
                   $("#time-slots-container").empty();

                   if (response.available_slots.length === 0) {
                       $("#time-slots-container").html(`
                       <div class="text-center py-4">
                       <div class="alert alert-warning">
                       <i class="bi bi-clock-history me-2"></i>
                       No hay fechas disponibles
                       </div>
                       <button class="btn btn-sm btn-outline-primary mt-2" onclick="updateCalendar()">
                       <i class="bi bi-arrow-left me-1"></i> Regresar al calendario
                       </button>
                       </div>
                       `);
                       return;
                   }

                   // Add slot duration info
                   $("#time-slots-container").append(`
                   <div class="slot-info mb-3">
                   <div class="d-flex justify-content-between align-items-center">
                   <small class="text-muted">
                   <i class="bi bi-info-circle me-1"></i>
                   Casillas: ${response.slot_duration} mins
                   ${response.break_duration ? ` | Descanso: ${response.break_duration} mins` : ''}
                   </small>

                   </div>
                   </div>
                   `);

                   // Add each time slot
                   const $slotsContainer = $("<div class='slots-grid'></div>");
                   response.available_slots.forEach(slot => {
                       const slotElement = $(`
                       <div class="time-slot btn btn-outline-primary mb-2"
                       data-start="${slot.start}"
                       data-end="${slot.end}"
                       title="Select ${slot.display}"
                       data-time="${slot.display}">
                       <i class="bi bi-clock me-1"></i>
                       ${slot.display}
                       </div>
                       `);

                       slotElement.on('click', function() {
                           $(".time-slot").removeClass("selected active");
                           $(this).addClass("selected active");
                           bookingState.selectedTime = {
                               start: $(this).data('start'),
                                      end: $(this).data('end'),
                                      display: $(this).text()
                           };
                           updateSummary();
                       });

                       $slotsContainer.append(slotElement);
                   });
                   $("#time-slots-container").append($slotsContainer);
               },
               error: function(xhr) {
                   $("#time-slots-container").html(`
                   <div class="text-center py-4">
                   <div class="alert alert-danger">
                   <i class="bi bi-exclamation-octagon me-2"></i>
                   Error loading availability
                   </div>
                   <button class="btn btn-sm btn-outline-primary mt-2" onclick="updateTimeSlots('${selectedDate}')">
                   <i class="bi bi-arrow-repeat me-1"></i> Try again
                   </button>
                   </div>
                   `);
               }
        });
    }



    function updateSummary() {
        $("#summary-employee").text(
            bookingState.selectedEmployee ? bookingState.selectedEmployee.user.name : 'No seleccionado');

        $("#summary-category").text(
            bookingState.selectedCategory ? bookingState.selectedCategory.title : 'No seleccionado');

        if (bookingState.selectedService) {
            $("#summary-service").text(
                `${bookingState.selectedService.title} (${bookingState.selectedService.price})`);
            $("#summary-duration").text(`${bookingState.selectedEmployee.slot_duration} minutos`);
            $("#summary-price").text(bookingState.selectedService.price);
        }

        if (bookingState.selectedDate && bookingState.selectedTime) {
            // Formato de fecha en español
            const meses = ["enero", "febrero", "marzo", "abril", "mayo", "junio",
            "julio", "agosto", "septiembre", "octubre", "noviembre", "diciembre"];
            const dias = ["domingo", "lunes", "martes", "miércoles", "jueves", "viernes", "sábado"];

            const fecha = new Date(bookingState.selectedDate);
            const diaSemana = dias[fecha.getDay()];
            const dia = fecha.getDate();
            const mes = meses[fecha.getMonth()];
            const año = fecha.getFullYear();

            const formattedDate = `${diaSemana}, ${dia} de ${mes} de ${año}`;

            $("#summary-datetime").text(
                `${formattedDate} a las ${bookingState.selectedTime.display || bookingState.selectedTime}`);
        }
    }



    function submitBooking() {
        // Get form data
        const form = $('#customer-info-form');
        const csrfToken = form.find('input[name="_token"]').val(); // Get CSRF token from form

        // Prepare booking data
        const bookingData = {
            employee_id: bookingState.selectedEmployee.id,
            service_id: bookingState.selectedService.id,
            name: $('#customer-name').val(),
                      email: $('#customer-email').val(),
                      phone: $('#customer-phone').val(),
                      obra_social: $('#customer-obra-social').val(),
                      notes: $('#customer-notes').val(),
                      amount: parseFloat(bookingState.selectedService.price.replace(/[^0-9.]/g, '')),
                      booking_date: bookingState.selectedDate,
                      booking_time: bookingState.selectedTime.start || bookingState.selectedTime,
                      status: 'Pending payment',
                      _token: csrfToken // Include CSRF token in payload
        };

        // Add user_id if authenticated (using JavaScript approach)
        if (typeof currentAuthUser !== 'undefined' && currentAuthUser) {
            bookingData.user_id = currentAuthUser.id;
        }

        // Show loading state
        const nextBtn = $("#next-step");
        nextBtn.prop('disabled', true).html(
            '<span class="spinner-border spinner-border-sm" role="status"></span> Processing...'
        );

        // Submit via AJAX
        $.ajax({
            url: "{{ url('bookings') }}",
               method: 'POST',
               data: bookingData,
               success: function(response) {
                   // Update modal with booking details
                   const rawDate = new Date(bookingState.selectedDate).toLocaleDateString(
                       'es-ES', {
                           weekday: 'long',
                           year: 'numeric',
                           month: 'long',
                           day: 'numeric'
                       });
                    
                    const formattedDate = rawDate.charAt(0).toUpperCase() + rawDate.slice(1);

                   const bookingDetails = `
                   <div class="mb-2"><strong>Cliente:</strong> ${$("#customer-name").val()}</div>
                   <div class="mb-2"><strong>Obra social:</strong> ${$("#customer-obra-social").val() || 'N/A'}</div>
                   <div class="mb-2"><strong>Servicio:</strong> ${bookingState.selectedService.title}</div>
                   <div class="mb-2"><strong>Profesional:</strong> ${bookingState.selectedEmployee.user.name}</div>
                   <div class="mb-2"><strong>Fecha y Hora:</strong> ${formattedDate} a las ${bookingState.selectedTime.display || bookingState.selectedTime}</div>
                   <div class="mb-2"><strong>Monto:</strong> ${bookingState.selectedService.price}</div>
                   <div><strong>Referencia:</strong> ${response.booking_id || 'BK-' + Math.random().toString(36).substr(2, 8).toUpperCase()}</div>
                   `;

                   $('#modal-booking-details').html(bookingDetails);

                   // Show success modal
                   const successModal = new bootstrap.Modal('#bookingSuccessModal');
                   successModal.show();

                   // Reset form after delay
                   setTimeout(resetBooking, 1000);
               },
               error: function(xhr) {
                   let errorMessage = 'Error al reservar el turno. Inténtelo nuevamente.';

        if (xhr.responseJSON && xhr.responseJSON.message) {
            errorMessage = xhr.responseJSON.message;
        } else if (xhr.status === 422) {
            errorMessage = 'Error de validación. Por favor, revisa tu información.';
        }

        alert(errorMessage);
        nextBtn.prop('disabled', false).html(
            'Avanzar <i class="bi bi-check-circle"></i>');
               },
               complete: function() {
                   // Re-enable button if request fails
                   if (nextBtn.prop('disabled')) {
                       setTimeout(() => {
                           nextBtn.prop('disabled', false).html(
                               'Confirmar turno <i class="bi bi-check-circle"></i>');
                       }, 2000);
                   }
               }
        });
    }

    function resetBooking() {
        // Reset booking state
        bookingState = {
            currentStep: 1,
            selectedEmployee: null,
            selectedCategory: null,
            selectedService: null,
            selectedDate: null,
            selectedTime: null
        };

        // Reset UI
        $(".employee-card, .category-card, .service-card, .calendar-day, .time-slot").removeClass(
            "selected");
        $("#customer-info-form")[0].reset();

        // Go to first step
        goToStep(1);
    }
});
</script>

@if ($setting->footer)
{!! $setting->footer !!}
@endif
</body>

</html>
