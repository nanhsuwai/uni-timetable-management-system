import {
  mdiMenu,
  mdiClockOutline,
  mdiCloud,
  mdiCrop,
  mdiAccount,
  mdiCogOutline,
  mdiEmail,
  mdiLogout,
  mdiThemeLightDark,
  mdiGithub,
  mdiOfficeBuildingCog,mdiChairRolling,
  mdiReact,
} from "@mdi/js";

export default [
  // {
  //   icon: mdiCogOutline,
  //   label: "Setting",
  //   menu: [
  //     {
  //       route: "ministries:all",
  //       icon: mdiOfficeBuildingCog,
  //       label: "Ministries / Organizations",
  //     },
  //     // {
  //     //   route: "registered:candidates:all",
  //     //   icon: mdiChairRolling,
  //     //   label: "Position",
  //     // },
  //     // {
  //     //   route: "nrc:all",
  //     //   icon: mdiChairRolling,
  //     //   label: "NrcData",
  //     // },
  //     // {
  //     //   isDivider: true,
  //     // },
  //     // {
  //     //   icon: mdiCrop,
  //     //   label: "Item Last",
  //     // },
  //   ],
  // },
  // {
  //   icon: mdiThemeLightDark,
  //   label: "Light/Dark",
  //   isDesktopNoLabel: true,
  //   isToggleLightDark: true,
  // },

  {
    icon: mdiThemeLightDark,
    label: "Light/Dark",
    isDesktopNoLabel: true,
    isToggleLightDark: true,
  },
  
  {
    isCurrentUser: true,
    menu: [
      {
        icon: mdiLogout,
        label: "Log Out",
        isLogout: true,
        route:"logout"
      },
    ],
  }

  // {
  //   icon: mdiGithub,
  //   label: "GitHub",
  //   isDesktopNoLabel: true,
  //   href: "https://github.com/justboil/admin-one-vue-tailwind",
  //   target: "_blank",
  // },
  // {
  //   icon: mdiReact,
  //   label: "React version",
  //   isDesktopNoLabel: true,
  //   href: "https://github.com/justboil/admin-one-react-tailwind",
  //   target: "_blank",
  // },
  // {
  //   icon: mdiLogout,
  //   label: "Log out",
  //   isDesktopNoLabel: true,
  //   isLogout: true,
  // },
];
