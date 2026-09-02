<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chatbot</title>
        <!--icons-->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
   <link rel="shortcut icon" href="{{ asset('images/icon.png') }}">

    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
</head>
<body>


            <!--BUTTON-->
           <div class="position-fixed bottom-0 end-0 m-4"
                style="z-index: 1050;">

                <button type="button"
                        class="btn rounded-circle shadow d-flex align-items-center justify-content-center"
                        style="
                            width: 60px;
                            height: 60px;
                            background-color: #0E7F77;
                            color: white;
                        "
                        popovertarget="popover-">

                    <i data-lucide="fish"
                    style="width: 28px; height: 28px;">
                    </i>

                </button>

            </div>


                    <!-- AI CHATBOT -->
            <div id="popover-"
                popover
                style="
                    position: fixed;
                    right: 24px;
                    bottom: 95px;
                    left: auto;
                    top: auto;

                    width: 360px;
                    max-width: calc(100vw - 30px);
                    height: 500px;
                    max-height: calc(100vh - 120px);

                    padding: 0;
                    margin: 0;

                    border: none;
                    border-radius: 16px;
                    overflow: hidden;

                    box-shadow: 0 8px 30px rgba(0,0,0,0.20);

                    z-index: 1049;
                ">

                <!-- Header -->
                <div class="d-flex align-items-center justify-content-between px-3 py-3"
                    style="background-color: #0E7F77; color: white;">

                    <div class="d-flex align-items-center">

                        <i data-lucide="fish"
                        class="me-2"
                        style="width: 24px; height: 24px;">
                        </i>

                        <div>
                            <div class="fw-bold">
                                AquaGuide
                            </div>

                            <small>
                                Your Fisheries Research Assistant
                            </small>
                        </div>

                    </div>

                    <button type="button"
                            popovertarget="popover-"
                            popovertargetaction="hide"
                            class="btn text-white">

                        <i data-lucide="x"
                        style="width: 20px; height: 20px;">
                        </i>

                    </button>

                </div>




                <!-- Messages -->
                <div class="p-3"
                    style="
                        height: calc(100% - 120px);
                        overflow-y: auto;
                        background-color: #f8fafc;
                    ">

                    <div class="d-flex mb-3">

                        <div class="rounded-3 px-3 py-2"
                            style="
                                background-color: white;
                                border: 1px solid #e5e7eb;
                                max-width: 85%;
                            ">

                            Hello! I'm AquaGuide, your Fisheries Research Assistant.
                            <br><br>
                            I can help you:
                            <br><br>
                            • Search research papers
                            <br>
                            • Generate citations
                            <br>
                            • Suggest research titles
                            <br>
                            • Summarize studies
                            <br>
                            • Explore fisheries topics
                            <br>
                            • Find related studies
                            <br>
                            • Answer system questions
                            <br>
                            • Provide repository analytics
                            <br><br>

                            How can I assist you today?

                        </div>

                    </div>

                </div>


                <!-- Input Mess-->
                <div class="border-top bg-white ">

                    <div class="input-group">

                        <input type="text"
                            class="form-control"
                            placeholder="Ask something...">

                        <button class="btn text-white"
                                style="background-color: #0E7F77;">

                            <i data-lucide="send"
                            style="width: 18px; height: 18px;">
                            </i>

                        </button>

                    </div>

                </div>

            </div>



            <!--icpn--->
            <script src="https://unpkg.com/lucide@latest"></script>
            <script>
                lucide.createIcons();
            </script>
</body>
</html>
