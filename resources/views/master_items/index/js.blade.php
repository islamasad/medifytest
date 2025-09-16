<script src="https://code.jquery.com/jquery-3.5.1.js"></script>
<script src="https://cdn.datatables.net/1.12.1/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.12.1/js/dataTables.bootstrap5.min.js"></script>

<script>
        // Inisialisasi DataTable
        var dataTableObj = $('#table').DataTable({
            searching: true,
            ordering: true,
            order: [[0, 'desc']],
            language: {
                url: '//cdn.datatables.net/plug-ins/1.12.1/i18n/id.json'
            },
            columns: [
                { data: 'kode' },
                { data: 'nama' },
                { data: 'jenis' },
                { 
                    data: 'harga_beli',
                    render: function(data, type, row) {
                        return formatRupiah(data);
                    }
                },
                { data: 'laba' },
                { 
                    data: null,
                    render: function(data, type, row) {
                        var harga_jual = row.harga_beli + (row.harga_beli * row.laba / 100);
                        return formatRupiah(Math.round(harga_jual));
                    }
                },
                { data: 'supplier' },
                {
                    data: null,
                    render: function(data, type, row) {
                        return `<a href="/master-items/view/${row.kode}" class="btn btn-primary btn-sm btn-action">View</a>`;
                    }
                }
            ]
        });

        $(document).ready(function() {
            // Load data pertama kali
            getData();
            
            // Event listener untuk tombol pencarian
            $('.btn-get-data').click(function() {
                getData();
            });

            // Event listener untuk tombol reset
            $('#reset-filters').click(function() {
                $('#filter-kode').val('');
                $('#filter-nama').val('');
                $('#filter-harga-min').val('');
                $('#filter-harga-max').val('');
                getData();
            });

            // Event listener untuk tekan Enter pada input field
            $('.form-control').keypress(function(e) {
                if (e.which === 13) {
                    getData();
                }
            });
        });

        function getData() {
            $('#loading-filter').show();
            
            var filter_kode = $('#filter-kode').val();
            var filter_nama = $('#filter-nama').val();
            var filter_harga_min = $('#filter-harga-min').val();
            var filter_harga_max = $('#filter-harga-max').val();

            // Bersihkan tabel sebelum memuat data baru
            dataTableObj.clear().draw();

            $.ajax({
                url: '/master-items/search',
                method: 'GET',
                dataType: 'json',
                data: {
                    kode: filter_kode,
                    nama: filter_nama,
                    hargamin: filter_harga_min,
                    hargamax: filter_harga_max
                },
                tryCount: 0,
                retryLimit: 3,
                success: function(results) {
                    if (results.status === 200 && results.data.length > 0) {
                        // Tambahkan data ke DataTable
                        dataTableObj.rows.add(results.data).draw();
                    } else {
                        // Tampilkan pesan jika tidak ada data
                        dataTableObj.clear().draw();
                        var colspan = dataTableObj.columns().count();
                        $('#table tbody').html(
                            '<tr><td colspan="' + colspan + '" class="text-center">Tidak ada data yang ditemukan</td></tr>'
                        );
                    }
                    $('#loading-filter').hide();
                },
                error: function(xhr, textStatus, errorThrown) {
                    this.tryCount++;
                    if (this.tryCount <= this.retryLimit) {
                        // Coba lagi jika gagal
                        $.ajax(this);
                        return;
                    }
                    
                    // Tampilkan pesan error
                    dataTableObj.clear().draw();
                    var colspan = dataTableObj.columns().count();
                    $('#table tbody').html(
                        '<tr><td colspan="' + colspan + '" class="text-center text-danger">Terjadi kesalahan saat memuat data</td></tr>'
                    );
                    $('#loading-filter').hide();
                }
            });
        }

        // Fungsi untuk format mata uang Rupiah
        function formatRupiah(amount) {
            return new Intl.NumberFormat('id-ID', {
                style: 'currency',
                currency: 'IDR',
                minimumFractionDigits: 0
            }).format(amount);
        }
    </script>