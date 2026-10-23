<body>

    <!-- ======= Header ======= -->
    <header id="header" class="header fixed-top d-flex align-items-center">
  
      <div class="d-flex align-items-center justify-content-between">
        <a href="/admin/index" class="logo d-flex align-items-center">
          <img src="{{ asset('frontend/images/logo.png') }}" alt="">
          <span class="d-none d-lg-block">Admin</span>
        </a>
        <i class="bi bi-list toggle-sidebar-btn"></i>
      </div><!-- End Logo -->
  

 
  
    </header><!-- End Header -->
  
    <!-- ======= Sidebar ======= -->
    <aside id="sidebar" class="sidebar">
  
      <ul class="sidebar-nav" id="sidebar-nav">
  
        <li class="nav-item">
          <a class="nav-link " href="/admin/index">
            <i class="bi bi-grid"></i>
            <span>Dashboard</span>
          </a>
        </li><!-- End Dashboard Nav -->
      
      <li class="nav-item">
          <a class="nav-link collapsed" href="/admin/category">
            <i class="bi bi-bookmark-plus"></i>
            <span>Category</span>
          </a>
        </li><!-- End Login Page Nav -->


      <li class="nav-item">
          <a class="nav-link collapsed" data-bs-target="#forms-cat" data-bs-toggle="collapse" href="#">
            <i class="bi bi-badge-ad-fill"></i><span>Adverts</span><i class="bi bi-chevron-down ms-auto"></i>
          </a>
          <ul id="forms-cat" class="nav-content collapse " data-bs-parent="#sidebar-nav">
            <li>
              <a href="/admin/active-adverts">
                <i class="bi bi-circle"></i><span>Active Advert</span>
              </a>
            </li>
            <li>
              <a href="/admin/disabled-adverts">
                <i class="bi bi-circle"></i><span>Disabled Adverts</span>
              </a>
            </li>
            <li>
              <a href="/admin/sold-adverts">
                <i class="bi bi-circle"></i><span>Sold Adverts</span>
              </a>
            </li>

            </ul>
        </li><!-- End Forms Nav -->
  
        <li class="nav-item">
          <a class="nav-link collapsed" data-bs-target="#forms-nav" data-bs-toggle="collapse" href="#">
            <i class="bi bi-people"></i><span>Users</span><i class="bi bi-chevron-down ms-auto"></i>
          </a>
          <ul id="forms-nav" class="nav-content collapse " data-bs-parent="#sidebar-nav">
            <li>
              <a href="/admin/active-users">
                <i class="bi bi-circle"></i><span>Active User</span>
              </a>
            </li>
            <li>
              <a href="/admin/unverified-users">
                <i class="bi bi-circle"></i><span>Unverified Users</span>
              </a>
            </li>
            <li>
              <a href="/admin/disabled-users">
                <i class="bi bi-circle"></i><span>Disabled Users</span>
              </a>
            </li>
            <li>
              <a href="/admin/user-verification">
                <i class="bi bi-circle"></i><span>User Verification</span>
              </a>
            </li>

          </ul>
        </li><!-- End Forms Nav -->

        <li class="nav-item">
          <a class="nav-link collapsed" data-bs-target="#forms-buy" data-bs-toggle="collapse" href="#">
            <i class="bi bi-cash-coin"></i><span>Buy Direct</span><i class="bi bi-chevron-down ms-auto"></i>
          </a>
          <ul id="forms-buy" class="nav-content collapse " data-bs-parent="#sidebar-nav">
            <li>
              <a href="/admin/completed-payments">
                <i class="bi bi-circle"></i><span>Completed Payments</span>
              </a>
            </li>
            <li>
              <a href="/admin/pending-payments">
                <i class="bi bi-circle"></i><span>Pending Payments</span>
              </a>
            </li>
          </ul>
        </li><!-- End Forms Nav -->

        <li class="nav-item">
          <a class="nav-link collapsed" data-bs-target="#settlements" data-bs-toggle="collapse" href="#">
            <i class="bi bi-cash-coin"></i><span>Settlements</span><i class="bi bi-chevron-down ms-auto"></i>
          </a>
          <ul id="settlements" class="nav-content collapse " data-bs-parent="#sidebar-nav">
            <li>
              <a href="/admin/pending-settlements">
                <i class="bi bi-circle"></i><span>Pending Settlements</span>
              </a>
            </li>
            <li>
              <a href="/admin/completed-settlements">
                <i class="bi bi-circle"></i><span>Completed Settlements</span>
              </a>
            </li>
          </ul>
        </li><!-- End Forms Nav -->

        <li class="nav-item">
          <a class="nav-link collapsed" data-bs-target="#boost" data-bs-toggle="collapse" href="#">
            <i class="bi bi-badge-ad"></i><span>Ad Boost</span><i class="bi bi-chevron-down ms-auto"></i>
          </a>
          <ul id="boost" class="nav-content collapse " data-bs-parent="#sidebar-nav">
            <li>
              <a href="/boost/active">
                <i class="bi bi-circle"></i><span>Active Boost</span>
              </a>
            </li>
            <li>
              <a href="/boost/completed">
                <i class="bi bi-circle"></i><span>Completed Boost</span>
              </a>
            </li>
            <li>
              <a href="/boost/unpaid">
                <i class="bi bi-circle"></i><span>Unpaid Boost</span>
              </a>
            </li>
          </ul>
        </li><!-- End Forms Nav -->
        
        <li class="nav-item">
          <a class="nav-link collapsed" data-bs-target="#advertising" data-bs-toggle="collapse" href="#">
            <i class="bi bi-badge-ad"></i><span>Advertising</span><i class="bi bi-chevron-down ms-auto"></i>
          </a>
          <ul id="advertising" class="nav-content collapse " data-bs-parent="#sidebar-nav">
            <li>
              <a href="/admin/create-advert">
                <i class="bi bi-circle"></i><span>Manage Adverts</span>
              </a>
            </li>

          </ul>
        </li><!-- End Forms Nav -->


        <li class="nav-item">
          <a class="nav-link collapsed" data-bs-target="#blog" data-bs-toggle="collapse" href="#">
            <i class="bi bi-badge-ad"></i><span>Blog</span><i class="bi bi-chevron-down ms-auto"></i>
          </a>
          <ul id="blog" class="nav-content collapse " data-bs-parent="#sidebar-nav">
            <li>
              <a href="/admin/blogs">
                <i class="bi bi-circle"></i><span>All Posts</span>
              </a>
            </li>

            <li>
              <a href="/admin/blogs/create">
                <i class="bi bi-circle"></i><span>New Posts</span>
              </a>
            </li>

          </ul>
        </li><!-- End Forms Nav -->

        <li class="nav-item">
          <a class="nav-link collapsed" data-bs-target="#reports" data-bs-toggle="collapse" href="#">
            <i class="bi bi-book"></i><span>Reports</span><i class="bi bi-chevron-down ms-auto"></i>
          </a>
          <ul id="reports" class="nav-content collapse " data-bs-parent="#sidebar-nav">
            <li>
              <a href="/admin/view-reports">
                <i class="bi bi-circle"></i><span>View Reports</span>
              </a>
            </li>

          </ul>
        </li><!-- End Forms Nav -->
        

        <li class="nav-item">
          <a class="nav-link collapsed" data-bs-target="#forms-setting" data-bs-toggle="collapse" href="#">
            <i class="bi bi-gear"></i><span>Settings</span><i class="bi bi-chevron-down ms-auto"></i>
          </a>
          <ul id="forms-setting" class="nav-content collapse " data-bs-parent="#sidebar-nav">

            <li>
              <a href="/settings/setup-shipping">
                <i class="bi bi-circle"></i><span>Set Up Shipping</span>
              </a>
            </li>

            <li>
              <a href="/settings/gig-locations">
                <i class="bi bi-circle"></i><span>GIG Locations</span>
              </a>
            </li>

            <li>
              <a href="/settings/manage-admins">
                <i class="bi bi-circle"></i><span>Manage Admins</span>
              </a>
            </li>

            <li>
              <a href="/settings/roles">
                <i class="bi bi-circle"></i><span>Manage Admins Roles</span>
              </a>
            </li>
   
            <li>
              <a href="/settings/change-password">
                <i class="bi bi-circle"></i><span>Change Password</span>
              </a>
            </li>
          </ul>
        </li><!-- End Forms Nav -->

       

        
  
        <li class="nav-item">
          <a class="nav-link collapsed" href="/admin/logout">
            <i class="bi bi-box-arrow-in-right"></i>
            <span>Logout</span>
          </a>
        </li><!-- End Login Page Nav -->
  
       
  
      </ul>
  
    </aside><!-- End Sidebar-->
  
