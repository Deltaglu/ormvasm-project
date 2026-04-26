<div class="d-flex gap-2 justify-content-end">
    <form action="{{ route('trash.restore', [$type, $id]) }}" method="POST">
        @csrf
        <button type="submit" class="btn btn-sm btn-outline-success d-flex align-items-center gap-1">
            <i class="bi bi-arrow-counterclockwise"></i> Restaurer
        </button>
    </form>
    <form action="{{ route('trash.force-delete', [$type, $id]) }}" method="POST" onsubmit="return confirm('Attention: Cette action est irréversible. Supprimer définitivement ?')">
        @csrf
        @method('DELETE')
        <button type="submit" class="btn btn-sm btn-outline-danger d-flex align-items-center gap-1">
            <i class="bi bi-trash"></i> Supprimer
        </button>
    </form>
</div>
