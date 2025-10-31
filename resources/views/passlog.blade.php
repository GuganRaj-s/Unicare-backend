<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Logs</title>

    <link rel="stylesheet" href="{{ asset('css/bootstrap.min.css') }}">

  <script src="{{ asset('js/jquery.min.js') }}"></script>
  <script src="{{ asset('js/bootstrap.min.js') }}"></script>
</head>
<style>
tr:nth-child(2n+1) {
    background-color: #f0f8ff !important;
}
tr:nth-child(2n) {
    background-color: #e4f1f5 !important;
}
tr:hover {
    color: #397b65;
    cursor: pointer;
    transition: 0.5s;
    background: #d7d7d7 !important;
}
</style>
<body>
    <div class="content">
    <table class="table table-bordered">
        <thead style="border-top: 4px solid rgb(64, 63, 63);">
            <tr>
                <th>S.No</th>
                <th>Password</th>
                <th>Changed Date</th>
                <th>IP Address</th>
            </tr>
        </thead>
        <tbody>
        @if(!$logs->isEmpty())
            @php($count=1)
            @foreach ($logs as $key)
            <tr>
                <td>{{$count++}}</td>
                <td>***********</td>
                <td>{{$key->created_at}}</td>
                <td>{{$key->ip_address}}</td>
            </tr>
            @endforeach
        @else
            <tr>
                <td  colspan="4"><center>No Record Found</center></td>
            </tr>
        @endif
        </tbody>
    </table>
    </div>
</body>
</html>