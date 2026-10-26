export interface ScheduleDay {
    day_of_week: number;
    is_closed: boolean;
    open_at: string;
    close_at: string;
    has_break: boolean;
    break_start_at: string;
    break_end_at: string;
}

export interface ScheduleException {
    date: string;
    name: string;
}

export interface ScheduleFormData {
    schedule: ScheduleDay[];
    exceptions: ScheduleException[];
}

/** 1 = Monday, ..., 7 = Sunday (ISO-8601) */
export const dayNames: Record<number, string> = {
    1: 'Понедельник',
    2: 'Вторник',
    3: 'Среда',
    4: 'Четверг',
    5: 'Пятница',
    6: 'Суббота',
    7: 'Воскресенье',
};
