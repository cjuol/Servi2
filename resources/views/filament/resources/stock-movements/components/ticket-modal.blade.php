{{-- Contenido del ticket centrado --}}
<div class="flex justify-center items-center bg-gray-50 dark:bg-gray-900 rounded-lg p-8" style="min-height: 500px;">
    <div class="w-[320px] max-w-full">
        <iframe 
            id="ticket-modal-frame"
            src="{{ $ticketUrl }}" 
            class="w-full border-0 bg-white rounded-lg shadow-2xl"
            style="height: 600px; display: block;"
            scrolling="no"
        ></iframe>
    </div>
</div>
