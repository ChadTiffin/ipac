import Login from './pages/Login'
import MyProfile from './pages/MyProfile'
import UserManager from './pages/UserManager'
import SectionTemplateEditor from './pages/SectionTemplateEditor'
import SectionTemplates from './pages/SectionTemplates'
import ReportTemplateEditor from './pages/ReportTemplateEditor'
import ReportTemplates from './pages/ReportTemplates'
import Clients from './pages/Clients'
import Reports from './pages/Reports'
import ReportNew from './pages/ReportNew'
import ReportEditor from './pages/ReportEditor'
import ReportViewer from './pages/ReportViewer'
import CompanySettings from './pages/CompanySettings'
import ClientEditor from './pages/ClientEditor'
import Audits from './pages/Audits'
import AuditForm from './pages/AuditForm'

export default [

  {
    path: '/', 
    component: Login, 
  },
  {
    path: '/login' ,
    component: Login,
  },
  {
    path: '/reset-password/:token' ,
    component: Login
  },
  {
    path: '/reports/client/:id' , 
    component: Reports,
    meta: {
      icon: "fa-wpforms",
      navbar: false,
      titleText: "Reports"
    }
  },
  {
    path: '/reports/new' , 
    component: ReportNew,
    meta: {
      icon: "fa-wpforms",
      navbar: false,
      titleText: "New Report"
    }
  },
  {
    path: '/audits/client/:id' , 
    component: Audits,
    meta: {
      icon: "fa-balance-scale",
      navbar: false,
      titleText: "Audits"
    }
  },
  {
    path: '/audits/form/:id' , 
    component: AuditForm,
    meta: {
      icon: "fa-balance-scale",
      navbar: false,
      titleText: "Audit Form"
    }
  },
  {
    path: '/clients' , 
    component: Clients,
    meta: {
      icon: "fa-users",
      navbar: true,
      titleText: "Clients"
    }
  },
  {
    path: '/clients/:id' , 
    component: ClientEditor,
    meta: {
      icon: "fa-user",
      navbar: false,
      titleText: "Edit Client"
    }
  },
  {
    path: '/templates/sections' , 
    component: SectionTemplates,
    meta: {
      icon: "fa-clone",
      navbar: true,
      titleText: "Section Templates"
    }
  },
  {
    path: '/templates/sections/edit/:id' , 
    component: SectionTemplateEditor, 
    meta: {
      icon: "fa-clone",
      navbar: false,
      titleText: "Edit Section Template"
    }
  },
  {
    path: '/templates/reports' , 
    component: ReportTemplates,
    meta: {
      icon: "fa-clone",
      navbar: true,
      titleText: "Report Templates"
    }
  },
  {
    path: '/templates/reports/edit/:id' , 
    component: ReportTemplateEditor, 
    meta: {
      icon: "fa-clone",
      navbar: false,
      titleText: "Edit Report Template"
    }
  },
  {
    path: '/settings',
    component: CompanySettings,
    meta: {
      icon: "fa-gear",
      navbar: true,
      titleText: "Company Settings"
    }
  },
  {
    path: '/my-profile' ,
    component: MyProfile,
    meta: {
      icon: "fa-user-circle-o",
      navbar: true,
      titleText: "My Profile"
    }
  },
  {
    path: '/users' , 
    component: UserManager,
    meta: {
      icon: "fa-users",
      navbar: true,
      titleText: "Users"
    }
  },
  {
    path: '/logout',
    component: Login,
    meta: {
      icon: "fa-sign-out",
      navbar: true,
      titleText: "Logout"
    }
  },
  {
    path: '/reports/edit/:id',
    component: ReportEditor,
    meta: {
      icon: "fa-wpforms",
      navbar: false,
      titleText: "Edit Report"
    }
  },
  {
    path: '/reports/view/:id',
    component: ReportViewer,
    meta: {
      icon: "fa-eye",
      navbar: false,
      titleText: "Report Viewer"
    }
  },
]
