<div class="card-table p-12 rounded-2xl shadow-soft text-center text-gray-500">
    <i class="fas fa-exclamation-triangle text-5xl text-yellow-500 mb-4"></i>
    <h3 class="text-2xl font-bold text-gray-800">Vendor Not Assigned</h3>
    <p class="mt-2 text-lg">The vendor "{{ $vendor->name }}" does not currently have a stall assigned to them.</p>
    <button data-action="back-to-details"
        class="mt-8 bg-indigo-500 hover:bg-indigo-600 text-white font-bold px-6 py-3 rounded-lg transition-smooth shadow-lg">
        Back to Vendor Information
    </button>
</div>
