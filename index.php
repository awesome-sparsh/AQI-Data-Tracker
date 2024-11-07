<?php
// PHP Code - At the top to handle AJAX request
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
    // Database connection details
    $servername = "sorter.in";
    $username = "sortersaas";
    $password = "Trubros!123&";
    $database = "trubros_experiments";

    // Function to get data from aqi_data and return it as JSON
    function getAQIData() {
        global $servername, $username, $password, $database;

        // Create a connection
        $conn = new mysqli($servername, $username, $password, $database);

        // Check the connection
        if ($conn->connect_error) {
            die("Connection failed: " . $conn->connect_error);
        }

        // Get current page and limit
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;
        $offset = ($page - 1) * $limit;

        // SQL query to select data with pagination
        $sql = "SELECT * FROM aqi_data LIMIT $limit OFFSET $offset";
        $result = $conn->query($sql);

        // Get total records
        $countResult = $conn->query("SELECT COUNT(*) as total FROM aqi_data");
        $totalRows = $countResult->fetch_assoc()['total'];

        // Initialize an empty array to store data
        $data = [];

        // If rows are returned, fetch them into the array
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $data[] = $row; // Add each row to the data array
            }
        }

        // Close the database connection
        $conn->close();

        // Return data and pagination info as JSON
        return json_encode(['data' => $data, 'total' => $totalRows, 'page' => $page, 'limit' => $limit]);
    }

    // Call the function and echo the JSON output to return it as an AJAX response
    echo getAQIData();
    exit; // Stop further execution to avoid returning HTML when it's an AJAX request
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AQI Data Table</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #ffffff;
            color: #333;
            margin: 0;
            padding: 20px;
            box-sizing: border-box; /* Include padding in width calculations */
        }

        h1 {
            color: #0056b3; 
            text-align: center; /* Center the header */
            font-size: 2.5em; 
            margin-bottom: 20px; 
            text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.2); 
        }

        .sorting-container {
            display: flex; 
            justify-content: center; 
            align-items: center; 
            margin-bottom: 30px; 
            flex-wrap: wrap; /* Allow wrapping on small screens */
        }

        label {
            font-weight: bold; 
            margin-right: 10px; 
        }

        select {
            padding: 8px;
            margin-bottom: 20px;
            border: 1px solid #0056b3; 
            border-radius: 4px;
            font-size: 16px;
            color: #333;
            transition: border-color 0.3s; 
            min-width: 150px; /* Minimum width for select boxes */
        }

        select:hover {
            border-color: #004494; 
        }

        /* Scrollable table container */
        .table-container {
            overflow-x: auto; /* Enable horizontal scrolling */
            margin-top: 20px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th, td {
            padding: 12px;
            border: 1px solid #0056b3; 
            text-align: left;
        }

        th {
            background-color: #A3C1E0; 
            color: #ffffff; 
        }

        tr:nth-child(even) {
            background-color: #f2f2f2; 
        }

        tr:hover {
            background-color: #e6f7ff; 
        }

        .loader {
            display: none;
            border: 4px solid #f3f3f3; 
            border-top: 4px solid #0056b3; 
            border-radius: 50%;
            width: 30px;
            height: 30px;
            animation: spin 1s linear infinite;
            margin: 0 auto;
            margin-bottom: 20px;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        .fade {
            opacity: 0.3; 
        }

       .pagination {
           text-align:center;
           margin-top:20px;
       }
       
       .pagination a {
           padding:10px;
           margin-right:5px;
           border-radius:4px;
           background-color:#0056b3;
           color:white;
           text-decoration:none;
           transition:.3s ease-in-out; /* Smooth transition */
       }
       
       .pagination a.active {
           background-color:#004494;
           font-weight:bold; /* Bold for active page */
       }
       
       .pagination a:hover:not(.active) {
           background-color:#007bff; /* Lighter blue on hover */
       }
       
       .pagination button {
           padding:10px;
           margin-right:5px;
           border-radius:4px;
           background-color:#0056b3;
           color:white;
           border:none;
           cursor:pointer;
       }
       
       .pagination button:hover {
           background-color:#007bff; /* Lighter blue on hover */
       }

       @media (max-width: 768px) { 
           h1 { font-size: 2em; }
           select { font-size: 14px; }
           th, td { padding: 10px; }
       }

       @media (max-width: 480px) { 
           h1 { font-size: 1.5em; }
           .sorting-container { flex-direction: column; align-items:flex-start;}
           label { margin-bottom :5px ; }
           select { width :100%; margin-bottom :10px ; }
           
           .pagination button,
           .pagination a {
               padding :8px ;
               font-size :14px ;
               display:block ; /* Stack buttons vertically */
               margin :5px auto ; /* Center buttons */
               width :80%; /* Make buttons wider on small screens */
           }
       }
    </style>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body>

<div class="container">
    <h1>AQI Data Table</h1>
    <div class="sorting-container">

         <div class="sort" style="margin-right:auto;">
             <select id="sortSelect">
                 <option value="">Select Column to Sort by</option>
                 <option value="date_time">Date & Time</option>
                 <option value="aqi_25">Particulate Matter 2.5</option>
                 <option value="aqi_10">Particulate Matter 10</option>
                 <option value="humidity">Humidity</option>
                 <option value="temperature">Temperature</option>
                 <option value="location">Location</option>
             </select>
         </div>

         <div class="order">
             <select id="orderSelect">
                 <option value="">Order By</option>
                 <option value="asc">Low to High</option>
                 <option value="desc">High to Low</option>
             </select>
         </div>
     </div>

     <!-- Loader -->
     <div class="loader-container" id="loaderContainer" style="display:none;">
         <div class="loader" id="loader"></div>
     </div>

     <!-- Scrollable Table Container -->
     <div class="table-container">
         <table id="aqiTable">
             <thead>
                 <tr>
                     <th>S.No</th>
                     <th>Date & time</th>
                     <th>Particulate matter 2.5</th>
                     <th>Particulate matter 10</th>
                     <th>Humidity</th>
                     <th>Temperature</th>
                     <th>Location</th>
                 </tr>
             </thead>
             <tbody>
                 <!-- Table rows will be appended here by JavaScript -->
             </tbody>
         </table>
     </div>

     <!-- Pagination Container -->
     <div class="pagination" id="pagination">
         <!-- Left Button -->
         <button id="prevSetBtn" disabled>&laquo;</button>

         <!-- Page Numbers -->
         <span id="pageNumbers"></span>

         <!-- Right Button -->
         <button id="nextSetBtn">&raquo;</button>
     </div>

</div>

<script>
$(document).ready(function () {
    var currentPage = 1;
    var limit = 10;

    function fetchAQIData(page) {
         $('#loader').show(); // Show loader when fetching data
         $('body').addClass('fade'); // Fade background
         $.ajax({
             url : window.location.href,
             type:'GET',
             dataType:'json',
             data:{ page : page , limit : limit },
             success:function(response){
                 populateTable(response.data);
                 setupPagination(response.total, response.page);
             },
             error:function(xhr,status,error){
                 console.error("AJAX error:", error);
             },
             complete:function(){
                 $('#loader').hide(); // Hide loader after fetching is done
                 $('body').removeClass('fade'); // Restore background visibility
             }
         });
     }

     function populateTable(data) {
         var tbody = $('#aqiTable tbody');
         tbody.empty(); // Clear existing rows
         var sno = (currentPage - 1) * limit + 1;

         data.forEach(function(item) {
             var row = '<tr>';
             row += '<td>' + sno + '</td>';
             sno++;

             var dateTime = new Date(item.date_time);
             var hours = String(dateTime.getHours()).padStart(2,'0');
             var minutes = String(dateTime.getMinutes()).padStart(2,'0');
             
             var day = String(dateTime.getDate()).padStart(2,'0');
             var month = String(dateTime.getMonth() +1).padStart(2,'0');
             var year = dateTime.getFullYear();
             
             var formattedDateTime = hours + ':' + minutes + ' ' + day + '/' + month + '/' + year;

             row += '<td>' + formattedDateTime + '</td>';
             row += '<td>' + item.aqi_25 + '</td>';
             row += '<td>' + item.aqi_10 + '</td>';
             row += '<td>' + item.humidity + '</td>';
             row += '<td>' + item.temperature + '</td>';
             row += '<td>' + item.location + '</td>';
             row += '</tr>';
             
             tbody.append(row); // Append the new row to the table body
         });
     }

     function setupPagination(totalRecords, currentPage) {
         var totalPages = Math.ceil(totalRecords / limit);
         
         $('#pageNumbers').empty(); // Clear existing page numbers

         let startPage = Math.floor((currentPage - 1) / 5) * 5 + 1;

         for (let i = startPage; i <= Math.min(startPage + 4, totalPages); i++) {
              let pageLink = $('<a href="#">' + i + '</a>').data('page', i);
              if (i === currentPage) {
                  pageLink.addClass('active'); // Highlight current page
              }
              pageLink.click(function(e){
                  e.preventDefault();
                  currentPage=$(this).data('page');
                  fetchAQIData(currentPage); // Fetch data for selected page
              });
              $('#pageNumbers').append(pageLink);
          }

          $('#prevSetBtn').prop('disabled', startPage === 1); // Disable if on first set of pages
          $('#nextSetBtn').prop('disabled', startPage + 5 > totalPages); // Disable if on last set of pages

          $('#prevSetBtn').off('click').on('click', function() { // Previous set button functionality
              currentPage -= (currentPage % 5 === 1 ? -4 : -5);
              fetchAQIData(currentPage);
          });

          $('#nextSetBtn').off('click').on('click', function() { // Next set button functionality
              currentPage += (currentPage % 5 === 0 ? -4 : +5);
              fetchAQIData(currentPage);
          });
      }

      fetchAQIData(currentPage); // Fetch initial data

      $('#sortSelect,#orderSelect').on('change',function(){
          fetchAQIData(currentPage); // Refetch data on sort/order change
      });
});
</script>

</body>
</html>