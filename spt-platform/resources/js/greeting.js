// How the dashboards greet each role. Supervisors are addressed as "Officer".
const TITLES = {
    trainee: 'Trainee',
    supervisor: 'Officer',
    admin: 'Admin',
};

export const greetingFor = (role) => `Hello, ${TITLES[role] ?? 'Trainee'}`;
