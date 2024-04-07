@if (!$number || $number->connected)
    @php
        header("Location: " . URL::to(route('number.index')), true, 302);
        exit();
    @endphp 
@endif
<div class="rounded bg-white mb-3 p-3">
    <div class="border-dashed d-flex align-items-center w-100 rounded overflow-hidden" style="min-height: 250px;">
        <img id="qr-code" data="{{$instance}}" class="center fw-light" width="200px" src="https://play-lh.googleusercontent.com/1-hPxafOxdYpYZEOKzNIkSP43HXCNftVJVttoo4ucl7rsMASXW3Xr6GlXURCubE1tA=w3840-h2160-rw">
    </div>
</div>
{{-- <form action="{{route('number.connect', ['method' => 'name', 'instance'=> '1'])}}">
<button type="submit"></button>
</form> --}}
<script>
function updateImage() 
{
    // API endpoint to fetch a random dog image
    const apiUrl = '{{env('APP_URL'). "/api/qr-code/". $instance}}';

    // Make a GET request to the API endpoint
    fetch(apiUrl)
        .then(response => response.json())
        .then(data => {
            // Get the URL of the random dog image from the API response
            const imageUrl = data.base64;

            // Update the src attribute of the image element
            document.getElementById('qr-code').src = imageUrl;
        })
        .catch(error => console.error('Error fetching data:', error));
}
window.setTimeout(function(){ updateImage() }, 1000);
</script>

