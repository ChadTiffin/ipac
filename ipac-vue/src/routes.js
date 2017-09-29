import Login from './pages/Login'
import ResetPassword from './pages/ResetPassword'
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
import Projects from './pages/Projects'
import ProjectEditor from './pages/ProjectEditor'
import Templating from './pages/Templating'
import Dashboard from './pages/Dashboard'
import Phases from './pages/Phases'
import Expenses from './pages/Expenses'

export default [

  {
    path: '/', 
    component: Dashboard, 
    meta: {
      icon: "fa-dashboard",
      navbar: true,
      titleText: "Dashboard",
      navGroup: ["main",'top']
    }
  },
  {
    path: '/login' ,
    component: Login,
  },
  {
    path: '/reset-password/:token' ,
    component: ResetPassword
  },
  {
    path: '/reports/client/:id' , 
    component: Reports,
    meta: {
      icon: "fa-wpforms",
      navbar: false,
      titleText: "Reports",
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
    path: '/audits', 
    component: Audits,
    meta: {
      icon: "fa-balance-scale",
      navbar: true,
      navGroup: ["main"],
      titleText: "Recent Audits",
      offline: true
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
      navGroup: ["main","top"],
      titleText: "Clients",
      offline: true
    }
  },
  {
    path: '/clients/:id/:tab' , 
    component: ClientEditor,
    meta: {
      icon: "fa-user",
      navbar: false,
      titleText: "Edit Client"
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
    path: '/projects' , 
    component: Projects,
    meta: {
      icon: "fa-cube",
      navbar: true,
      navGroup: ["main","top"],
      titleText: "Projects"
    }
  },
  {
    path: '/projects/:id/:tab' , 
    component: ProjectEditor,
    meta: {
      icon: "fa-cube",
      navbar: false,
      titleText: "Project"
    }
  },
  {
    path: '/templates' , 
    component: Templating,
    meta: {
      icon: "fa-clone",
      navbar: true,
      navGroup: ["main"],
      titleText: "Report Templating"
    }
  },
  {
    path: '/templates/sections' , 
    component: Templating,
    meta: {
      icon: "fa-clone",
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
    component: Templating,
    meta: {
      icon: "fa-clone",
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
    path: '/phases' , 
    component: Phases,
    meta: {
      icon: "fa-cube",
      navbar: true,
      navGroup: ['main'],
      titleText: "Phases Master List"
    }
  },
  {
    path: '/settings',
    component: CompanySettings,
    meta: {
      icon: "fa-gear",
      navbar: true,
      navGroup: ["app"],
      titleText: "Company Settings"
    }
  },
  {
    path: '/my-profile' ,
    component: MyProfile,
    meta: {
      icon: "fa-user-circle-o",
      navbar: true,
      navGroup: ["app"],
      titleText: "My Profile"
    }
  },
  {
    path: '/users' , 
    component: UserManager,
    meta: {
      icon: "fa-users",
      navbar: true,
      navGroup: ["app"],
      titleText: "Users",
      perm: ["Admin","Root"]
    }
  },
  {
    path: '/logout',
    component: Login,
    meta: {
      icon: "fa-sign-out",
      navbar: true,
      navGroup: ["app"],
      titleText: "Logout",
      offline: true
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
  {
    path: '/expenses',
    component: Expenses,
    meta: {
      icon: "fa-money",
      navbar: true,
      navGroup: ["main"],
      titleText: "Expenses",
      perm: ["Admin", "Root"]
    }
  },
]
