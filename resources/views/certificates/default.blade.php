<!DOCTYPE html>
<html lang="ms">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sijil Penyertaan - {{ $attendee->event->title }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Cinzel:wght@400;700&family=Great+Vibes&family=Lato:wght@300;400;700&display=swap');
        .font-serif-display { font-family: 'Cinzel', serif; }
        .font-script { font-family: 'Great Vibes', cursive; }
        .bg-pattern {
            background-image: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%239C92AC' fill-opacity='0.05'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
        }
    </style>
</head>
<body class="bg-gray-100 min-h-screen flex items-center justify-center p-4 print:p-0">

    <div class="max-w-[800px] w-full bg-white border-8 border-double border-slate-200 p-10 shadow-2xl relative overflow-hidden text-center bg-pattern print:shadow-none print:border-4 print:w-full print:max-w-none">
        
        <!-- Border Decoration -->
        <div class="absolute top-4 left-4 right-4 bottom-4 border-2 border-slate-300 pointer-events-none"></div>
        <div class="absolute top-6 left-6 right-6 bottom-6 border border-slate-200 pointer-events-none"></div>

        <div class="relative z-10 py-12">
            <!-- Header -->
            <div class="mb-2 text-slate-500 tracking-[0.2em] uppercase text-sm font-semibold">Sijil Penyertaan</div>
            <h1 class="text-4xl md:text-5xl font-serif-display text-slate-800 mb-8 font-bold">CERTIFICATE OF PARTICIPATION</h1>
            
            <p class="text-lg text-slate-600 mb-4 italic">Dengan ini disahkan bahawa</p>
            
            <!-- Name -->
            <div class="text-4xl md:text-6xl font-script text-blue-900 mb-8 py-2 px-4 inline-block border-b-2 border-slate-100">
                {{ $attendee->name }}
            </div>
            
            <p class="text-lg text-slate-600 mb-6">Telah menyertai acara</p>
            
            <!-- Event Title -->
            <h2 class="text-2xl md:text-3xl font-serif-display text-slate-800 mb-12 font-bold">{{ $attendee->event->title }}</h2>
            
            <!-- Details -->
            <div class="flex flex-col md:flex-row justify-between items-end px-4 md:px-24 mt-12 gap-8 md:gap-0">
                <div class="text-center w-full md:w-auto">
                    <div class="h-px w-32 bg-slate-400 mb-2 mx-auto"></div>
                    <p class="text-sm text-slate-500 uppercase tracking-wider">Tarikh</p>
                    <p class="font-semibold text-slate-700">{{ $attendee->checked_in_at ? $attendee->checked_in_at->format('d F Y') : now()->format('d F Y') }}</p>
                </div>
                
                <div class="text-center w-full md:w-auto order-first md:order-none">
                     <div class="mb-2 flex justify-center">
                        {!! QrCode::size(60)->generate(route('certificates.show', $attendee->qr_code)) !!}
                     </div>
                    <p class="text-[10px] text-slate-400">ID: {{ substr($attendee->qr_code, 0, 8) }}</p>
                </div>

                <div class="text-center w-full md:w-auto">
                    <div class="h-px w-32 bg-slate-400 mb-2 mx-auto"></div>
                    <p class="text-sm text-slate-500 uppercase tracking-wider">Penganjur</p>
                    <p class="font-semibold text-slate-700">{{ $attendee->event->organizer->name ?? 'Penganjur' }}</p>
                </div>
            </div>
        </div>
    </div>
    
    <div class="fixed bottom-4 right-4 flex gap-2 print:hidden">
        <button onclick="window.print()" class="px-4 py-2 bg-slate-800 text-white rounded shadow hover:bg-slate-700">Cetak / Simpan PDF</button>
    </div>

</body>
</html>
