<aside id="layout-menu" class="layout-menu menu-vertical menu bg-menu-theme">
    <div class="app-brand demo">
      <a href="index.html" class="app-brand-link">
        <img src="https://upload.wikimedia.org/wikipedia/commons/3/39/Department_of_Labor_and_Employment_%28DOLE%29.svg" class="app-brand-logo w-px-30 h-auto me-2 " alt="logo" />
            <span class="app-brand-text menu-text fw-bold">OPCR
              <br />
              <span class="fs-tiny fw-medium"></span>
            </span>
      </a>

      <a href="javascript:void(0);" class="layout-menu-toggle menu-link text-large ms-auto">
        <i class="bx bx-chevron-left bx-sm align-middle"></i>
      </a>
    </div>

    <div class="menu-inner-shadow"></div>

    <ul class="menu-inner py-1">
        <!-- Page -->
        <li class="menu-item {{ request()->is('admin_dashboard') ? 'active' : '' }}">
          <a href="/dash-home" class="menu-link">
            <i class="menu-icon tf-icons bx bx-home-circle"></i>
            <div class="text-truncate" data-i18n="Page 1">Dashboard</div>
          </a>
        </li>
        <li class="menu-item">
          <div style="margin-left: 5%; margin-top: 5%; color: #b4b0c4;">Reports</div>
        </li>
        @if(Auth::user()->role->name === 'IT' || Auth::user()->role->name === 'SAP')
        <li class="menu-item {{ request()->is('outcome') ? 'active' : '' }}">
          <a href="/outcome" class="menu-link">
            <i class='menu-icon tf-icons bx bx-archive-in'></i>
            <div class="text-truncate" data-i18n="Page 2">Organizational Outcome</div>
            {{-- <span class="badge bg-danger badge-notifications p-1 fs-8">14</span> --}}
          </a>
        </li>
        @endif

        <li class="menu-item {{ request()->is('indicator') ? 'active' : '' }}">
          <a href="/indicator" class="menu-link">
            <i class='menu-icon tf-icons bx bx-plus-circle'></i>
            <div class="text-truncate" data-i18n="Page 2">Indicator</div>
          </a>
        </li>
       
        <li class="menu-item {{ request()->is('entries') ? 'active' : '' }}">
          <a href="/entries" class="menu-link">
            <i class='menu-icon tf-icons bx bx-file'></i>
            <div class="text-truncate" data-i18n="Page 2">Entries</div>
            <span class="badge {{ $entriesCount == 0 ? 'bg-warning' : 'bg-danger'}}  badge-notifications p-1 fs-8">{{$entriesCount}}</span>
          </a>
        </li> 
        
        @if(Auth::user()->role->name === 'IT' || Auth::user()->role->name === 'SAP')

        <li class="menu-item">
            <div style="margin-left: 5%; margin-top: 5%; color: #b4b0c4;">User Management</div>
          </li>

        <li class="menu-item {{ request()->is('roles') ? 'active' : '' }}">
          <a href="/roles" class="menu-link">
            <i class='menu-icon bx bx-purchase-tag-alt'></i>
            {{-- <i class='menu-icon tf-icons bx bx-group'></i> --}}
            <div class="text-truncate" data-i18n="Page 2">Role</div>
          </a>
        </li>
       

        <li class="menu-item {{ request()->is('user') ? 'active' : '' }}">
          <a href="/user" class="menu-link">
            <i class='menu-icon tf-icons bx bx-group'></i>
            <div class="text-truncate" data-i18n="Page 2">Users</div>
          </a>
        </li>

        <li class="menu-item" {{ request()->is('login_in') ? 'active' : '' }}>
            <a href="/login_in" class="menu-link">
              <i class='menu-icon tf-icons bx bx-history' ></i>
              <div class="text-truncate" data-i18n="Page 2">History</div>
            </a>
          </li>
          @endif
      </ul>
  </aside>
