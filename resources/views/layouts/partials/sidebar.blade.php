<aside class="sidebar" id="sidebar">
  <div class="sidebar-brand">
    <div class="brand-icon">
      <img src="{{ asset('images/logo.jpeg') }}" alt="MBOUP GAMING">
    </div>
    <div>
      <div class="brand-text">MBOUP GAMING</div>
      <div class="brand-sub">SI Boutique</div>
    </div>
  </div>

  <nav class="sidebar-nav">
    <div class="nav-section">Menu principal</div>
    @foreach($menuItems as $item)
      <a href="{{ route($item['route']) }}"
         class="nav-link {{ request()->routeIs($item['route']) || request()->routeIs(str_replace('.index', '.*', $item['route'])) ? 'active' : '' }}">
        <i class="bi {{ $item['icon'] }}"></i>
        <span>{{ $item['label'] }}</span>
      </a>
    @endforeach
  </nav>

  <div class="sidebar-footer">
    &copy; {{ date('Y') }} MBOUP GAMING SI
  </div>
</aside>
