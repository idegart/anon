export default [
    {
        path: '/',
        component: () => import(/* webpackChunkName: "page-main" */ `../pages/Main`),
    }
]
