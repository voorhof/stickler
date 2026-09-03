@props(['url'])
<tr>
<td class="header">
<a href="{{ $url }}" style="display: inline-block;">
<img src="{{ asset('art/logo/pietjeprecies_logo_sm.png') }}"
     class="logo"
     alt="{{ config('app.name') }}"
     width="216" height="96"
     style="display: block; margin: 10px auto; max-width: 100%; height: auto;">
{!! $slot !!}
</a>
</td>
</tr>
