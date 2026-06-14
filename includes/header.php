<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>VidFolio - Multimedia Search and Retrieval System</title>
    
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/core@1.0.0-beta20/dist/css/tabler.min.css">
    
    <style>
        :root { --tblr-body-bg: #0c0e17; }
        .sidebar-premium { background-color: #141824; border-right: 1px solid #23293d; }
        .card-portfolio { background-color: #141824; border: 1px solid #23293d; border-radius: 12px; overflow: hidden; transition: all 0.25s ease; }
        .card-portfolio:hover { transform: translateY(-5px); border-color: #4263eb; box-shadow: 0 12px 20px rgba(0,0,0,0.35); }
        .thumb-container { height: 165px; display: flex; align-items: center; justify-content: center; position: relative; font-weight: 700; background-size: cover; background-position: center; }
        .color-selector { display: inline-block; width: 30px; height: 30px; border-radius: 50%; margin-right: 6px; cursor: pointer; border: 3px solid transparent; transition: all 0.2s; }
        .color-selector.active { border-color: #ffffff; transform: scale(1.15); }
        .form-dark { background-color: #1e2438 !important; border: 1px solid #2d3654 !important; color: #ffffff !important; border-radius: 8px; }
        .form-dark:focus { border-color: #4263eb; }
        .form-dark::placeholder { color: #64748b; }
    </style>
</head>
<body class="theme-dark">